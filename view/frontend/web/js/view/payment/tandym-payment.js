/**
 * @category    Tandym
 * @package     Tandym_Tandympay
 * @copyright   Copyright (c) Tandym (https://www.bytandym.com/)
 */
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
                type: 'tandympay',
                component: 'Tandym_Tandympay/js/view/payment/method-renderer/tandym'
            }
        );

        /** Add view logic here if needed */
        return Component.extend({});
    }
);
