<?php

namespace Ime\CatalogViewMode\Plugin;

use Magento\Customer\Model\Session;

class CategoryViewMode
{
    /**
     * @var Session
     */
    protected $customerSession;

    public function __construct(Session $customerSession)
    {
        $this->customerSession = $customerSession;
    }

    public function afterGetCurrentMode(
        \Magento\Catalog\Block\Product\ProductList\Toolbar $subject,
        $result
    ) {
        // If not logged in → keep original default (usually grid)
        if (!$this->customerSession->isLoggedIn()) {
            return $result;
        }

        // If user already selected manually → respect selection
        $request = $subject->getRequest();
        if ($request->getParam('product_list_mode') || $request->getParam('mode')) {
            return $result;
        }

        // Otherwise set default to list for logged-in users
        return 'list';
    }
}
