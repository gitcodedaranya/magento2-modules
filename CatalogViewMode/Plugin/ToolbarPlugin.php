<?php

namespace Ime\CatalogViewMode\Plugin;

class ToolbarPlugin
{
    /** @var \Magento\Customer\Model\Session */
    protected $customerSession;

    /** @var \Magento\Catalog\Model\Session */
    protected $catalogSession;

    public function __construct(
        \Magento\Customer\Model\Session\Proxy $customerSession,
        \Magento\Catalog\Model\Session\Proxy $catalogSession
    ) {
        $this->customerSession = $customerSession;
        $this->catalogSession = $catalogSession;
    }

    /**
     * V14 ULTIMATE REPAIR FIX:
     * Part 1: Ensure the "starting point" for logged-in users is List.
     */
    public function afterGetDefaultMode($subject, $result)
    {
        if ($this->customerSession->isLoggedIn()) {
            return 'list';
        }
        return $result;
    }

    /**
     * Part 2: THE "ONE SHOT" BRIDGE.
     * Injects a tiny JS script to fix the theme's "dead" icons.
     * It transforms href="#" into a REAL clickable URL.
     */
    public function afterToHtml($subject, $result)
    {
        if (!$this->customerSession->isLoggedIn()) {
            return $result;
        }

        $jsFix = "
<script>
require(['jquery'], function($) {
    $(document).ready(function() {
        var fixViewModes = function() {
            $('.modes-mode').each(function() {
                var mode = $(this).attr('data-value');
                if (mode && ($(this).attr('href') === '#' || !$(this).attr('href'))) {
                    var url = new URL(window.location.href);
                    url.searchParams.set('product_list_mode', mode);
                    $(this).attr('href', url.toString());
                    // Remove data-role to bypass broken theme scripts
                    $(this).removeAttr('data-role');
                }
            });
        };
        fixViewModes();
        $(document).on('ajaxComplete', fixViewModes);
    });
});
</script>";
        return $result . $jsFix;
    }

    /**
     * Part 3: Control the final outcome with absolute priority.
     */
    public function aroundGetCurrentMode(
        \Magento\Catalog\Block\Product\ProductList\Toolbar $subject,
        \Closure $proceed
    ) {
        // VERIFICATION MARKER: V14-ULTIMATE-REPAIR-FIX

        if (!$this->customerSession->isLoggedIn()) {
            return $proceed();
        }

        $request = $subject->getRequest();
        $modeVar = $subject->getModeVarName() ?: 'product_list_mode';
        $requestedMode = $request->getParam($modeVar);

        // 1. URL priority (manual click)
        if ($requestedMode && in_array($requestedMode, ['grid', 'list'])) {
            $this->catalogSession->setData('display_mode', $requestedMode);
            return $requestedMode;
        }

        // 2. Session priority
        $sessionMode = $this->catalogSession->getData('display_mode');
        if ($sessionMode && in_array($sessionMode, ['grid', 'list'])) {
            return $sessionMode;
        }

        // 3. Fallback
        return 'list';
    }
}