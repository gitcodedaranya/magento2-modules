<?php
namespace Ime\AddMyProducts\Controller\Ajax;


use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Wishlist\Model\WishlistFactory;

class Addmyproducts extends Action
{
    protected $jsonFactory;
    protected $productFactory;
    protected $wishlistFactory;

    public function __construct(Context $context,  WishlistFactory $wishlistFactory, JsonFactory $jsonFactory, ProductRepositoryInterface $productRepository)
    {
        $this->wishlistFactory = $wishlistFactory;
        $this->jsonFactory = $jsonFactory;
        $this->productRepository = $productRepository;
      
        parent::__construct($context);
    }

    public function execute()
        {
            $result = $this->jsonFactory->create();
            $request = $this->getRequest();

            // Decide which internal function to call
            $action = $request->getParam('action'); // e.g., 'add', 'remove', 'update'

            try {
                switch ($action) {
                    case 'addtierprice':
                        $response = $this->addtierPrice($request);
                        break;
                     case 'deletetierprice':
                        $response = $this->deletetierprice($request);
                        break;
                    // case 'update':
                    //     $response = $this->updateFunction($request);
                    //     break;
                    default:
                        $response = ['success' => false, 'message' => 'Invalid action'];
                }
            } catch (\Exception $e) {
                $response = ['success' => false, 'message' => $e->getMessage()];
            }

            return $result->setData($response);
        }
    public function addtierprice()
    {
        try {
            $productId = $_REQUEST['productId'];
            $productSku = $_REQUEST['productSku'];
            $productPrice = $_REQUEST['productPrice'];
            $qty = $_REQUEST['qty'];
            $groupId = $_REQUEST['groupId'];
            $storeId = $_REQUEST['storeId'];
            $product = $this->productRepository->getById($productId, true);
           // echo "Pi: ".$productId." PRP: ".$productPrice." GI: ".$groupId." StId: ".$storeId;exit;
            $existingTierPrices = $product->getTierPrices();
           // print_r($existingTierPrices);exit;
            $tierPrices = [];

			 foreach ($existingTierPrices as $tierPrice) {
               // echo "L: ".$tierPrice->getCustomerGroupId();exit;
                $tierPrices[] = [
                    'website_id' => 0,
                    'cust_group' => $tierPrice->getCustomerGroupId(),
                    'price_qty' => $tierPrice->getQty(),
                    'price'  => $tierPrice->getValue()
                ];
            }
            //print_r($tierPrices);exit;
            
            $addedCount = 0;

			// if ($qty <= 0 || $productPrice <= 0) {
            //     continue; // skip invalid
            // }
            //echo $qty." ".$productPrice;exit;
              $exists = false;
               
            // Check if same group & qty already exists
                foreach ($tierPrices as $existing) {
                    if (
                        $existing['cust_group'] == $groupId &&
                        (float)$existing['price_qty'] == $qty &&
                        $existing['website_id'] == 0
                    ) {
                        $exists = true;
                        break;
                    }
                }

            if (!$exists) {
                $tierPrices[] = [
                'cust_group' => $groupId, // 0 for NOT LOGGED IN
                'price_qty' => $qty,
                "price_value_type"=>'fixed',
                'price' => $productPrice,
                'website_id' => 0 // 0 = All Websites, or specific website ID
                ];
                $addedCount++;
            }

           
            if ($addedCount > 0) {
                $product->setTierPrice($tierPrices);
                $this->productRepository->save($product);
            } 
            
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $groupRepository = $objectManager->get(\Magento\Customer\Api\GroupRepositoryInterface::class);
            $customerSession = $objectManager->get('Magento\Customer\Model\Session');
            $customerId = $customerSession->getId();
           // Load the customer's wishlist
            $wishlist = $this->wishlistFactory->create()->loadByCustomerId($customerId);

            if (!$wishlist->getId()) {
                throw new \Exception('Unable to load wishlist for this customer.');
            }

           // Add the product to the wishlist
            $wishlist->addNewItem($product);
            $wishlist->save();

            // Optional: Recalculate totals
            //$wishlist->getWishlistItemCollection()->save();

            return ['success' => true, 'message' => 'Product Added successfully'];

        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return ['success' => false, 'message' => 'Product not found'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function deletetierprice()
    {
        try{
            $productId = $_REQUEST['productId'];
            $productSku = $_REQUEST['productSku'];
            $productPrice = $_REQUEST['productPrice'];
            $qty = $_REQUEST['qty'];
            $groupId = $_REQUEST['groupId'];
            $storeId = $_REQUEST['storeId'];
            $product = $this->productRepository->getById($productId, true);
            // echo "Pi: ".$productId." PRP: ".$productPrice." GI: ".$groupId." StId: ".$storeId;exit;
            $existingTierPrices = $product->getTierPrices();
            $remainingTierPrices = [];
            $removedCount = 0;

            foreach ($existingTierPrices as $tierPrice) {
                $shouldRemove = false;
                $groupId = $groupId;
                $qty  = $qty;

                if (
                    $tierPrice->getCustomerGroupId() == $groupId &&
                    (float)$tierPrice->getQty() == $qty
                ) {
                    $shouldRemove = true;
                    $removedCount++;
                    break;
                }
                    
                
                // Keep the tier price if not marked for removal
                if (!$shouldRemove) {
                    $remainingTierPrices[] = [
                        'website_id' => $tierPrice->getWebsiteId(),
                        'cust_group' => $tierPrice->getCustomerGroupId(),
                        'price_qty'  => $tierPrice->getQty(),
                        'price'  => $tierPrice->getValue()
                    ];
                }
            } // end foreach
            $product->setTierPrice($remainingTierPrices);
            $this->productRepository->save($product);
            return ['success' => true, 'message' => 'Product Removed successfully'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}