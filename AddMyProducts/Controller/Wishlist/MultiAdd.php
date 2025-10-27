<?php
namespace Ime\AddMyProducts\Controller\Wishlist;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Wishlist\Model\WishlistFactory;
use Magento\Checkout\Model\Cart;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Catalog\Api\ProductRepositoryInterface;

class MultiAdd extends Action
{
    protected $wishlistFactory;
    protected $cart;
    protected $customerSession;
    protected $productRepository;
    public function __construct(
        Context $context,
        WishlistFactory $wishlistFactory,
        Cart $cart,
        CustomerSession $customerSession,
        ProductRepositoryInterface $productRepository
    ) {
        parent::__construct($context);
        $this->wishlistFactory = $wishlistFactory;
        $this->cart = $cart;
        $this->customerSession = $customerSession;
        $this->productRepository = $productRepository;
        
    }

    public function execute()
    {
        $qtys = $this->getRequest()->getParam('qty', []);
        $customerId = $this->customerSession->getCustomerId();
        //echo "<pre>";print_r($qtys);exit;
        if (empty($qtys)) {
            $this->messageManager->addErrorMessage(__('No quantities provided.'));
            return $this->_redirect('wishlist');
        }

        
        $wishlist = $this->wishlistFactory->create()->loadByCustomerId($customerId);
        $addedCount = 0;

        foreach ($qtys as $itemId => $qty) {
            $qty = (int)$qty;
            if ($qty <= 0) continue;

           // $item = $wishlist->getItem($itemId);
            $proObj = $this->productRepository->getById($itemId);
            if ($itemId && $proObj->isSalable()) {
                try {
                    $this->cart->addProduct($proObj, ['qty' => $qty]);
                    $addedCount++;
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                }
            }
        }

        $this->cart->save();

        if ($addedCount > 0) {
            $this->messageManager->addSuccessMessage(__('%1 product(s) added to cart.', $addedCount));
        } else {
            $this->messageManager->addNoticeMessage(__('No valid products to add.'));
        }

        return $this->_redirect('checkout/cart');
    }
}
