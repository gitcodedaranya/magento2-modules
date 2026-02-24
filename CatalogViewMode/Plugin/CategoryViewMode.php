<?php

namespace Ime\CatalogViewMode\Plugin;

use Magento\Customer\Model\Session;
use Magento\Framework\Stdlib\CookieManagerInterface;

class CategoryViewMode
{
    const COOKIE_NAME = 'display_mode';

    protected $customerSession;
    protected $cookieManager;

    public function __construct(
        Session $customerSession,
        CookieManagerInterface $cookieManager
    ) {
        $this->customerSession = $customerSession;
        $this->cookieManager = $cookieManager;
    }

    public function afterGetCurrentMode(
        \Magento\Catalog\Block\Product\ProductList\Toolbar $subject,
        $result
    ) {
        // Only for logged-in users
        if (!$this->customerSession->isLoggedIn()) {
            return $result;
        }

        // If cookie already set → respect user choice
        $cookieMode = $this->cookieManager->getCookie(self::COOKIE_NAME);
        if ($cookieMode) {
            return $cookieMode;
        }

        // No cookie → first load → default list
        return 'list';
    }
}
