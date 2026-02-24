define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'ccsave',
                component: 'Ime_CcSave/js/view/payment/method-renderer/ccsave'
            }
        );
        
        return Component.extend({});
    }
);