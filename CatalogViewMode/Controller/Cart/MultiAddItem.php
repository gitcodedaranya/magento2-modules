<?php
namespace Ime\CatalogViewMode\Controller\Wishlist;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Wishlist\Model\WishlistFactory;
use Magento\Checkout\Model\Cart;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Catalog\Api\ProductRepositoryInterface;

class MultiAddItem extends Action
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
        $products = json_decode($this->getRequest()->getParam('products'), true);
        echo "<pre>";print_r($products);exit;
    }
}
