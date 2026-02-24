<?php

namespace Ime\CatalogViewMode\Plugin\Product;

use Magento\Customer\Model\Session;
use Magento\Catalog\Block\Product\ProductList\Toolbar;

class ListToolbarPlugin
{
    protected $customerSession;

    public function __construct(Session $customerSession)
    {
        $this->customerSession = $customerSession;
    }

    public function beforeToHtml(Toolbar $subject)
    {
        if (!$this->customerSession->isLoggedIn()) {
            return;
        }

        // Only set default if mode not already selected
        if (!$subject->getRequest()->getParam('product_list_mode')
            && !$subject->getRequest()->getParam('mode')
        ) {
            $subject->setData('_current_grid_mode', 'list');
        }
    }
}
