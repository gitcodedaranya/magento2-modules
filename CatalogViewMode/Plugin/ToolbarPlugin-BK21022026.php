<?php

namespace Ime\CatalogViewMode\Plugin;

use Magento\Customer\Model\Session as CustomerSession;

class ToolbarPlugin
{
    protected $customerSession;

    public function __construct(CustomerSession $customerSession)
    {
        $this->customerSession = $customerSession;
    }

    /**
     * Change default view mode based on login status
     */
    public function afterGetCurrentMode(
        \Magento\Catalog\Block\Product\ProductList\Toolbar $subject,
        $result
    ) {
        // If customer is logged in List View
        if ($this->customerSession->isLoggedIn()) {
            return 'list';
        }

        // If guest grid
        return $result;
    }
}