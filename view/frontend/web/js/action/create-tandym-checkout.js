/**
 * @category    Tandym
 * @package     Tandym_Tandympay
 * @copyright   Copyright (c) Tandym (https://www.bytandym.com/)
 */

/**
 * @api
 */
define([
    'Magento_Checkout/js/model/quote',
    'Magento_Customer/js/model/customer',
    'Magento_Checkout/js/model/url-builder',
    'Tandym_Tandympay/js/model/create-tandym-checkout',
    'Magento_CheckoutAgreements/js/model/agreements-assigner',
], function (quote, customer, urlBuilder, tandymCheckoutService, agreementsAssigner) {
    'use strict';

    return function (paymentData, messageContainer) {
        var serviceUrl, payload;
        agreementsAssigner(paymentData);
        payload = {
            cartId: quote.getQuoteId(),
            billingAddress: quote.billingAddress(),
            paymentMethod: paymentData
        };

        if (customer.isLoggedIn()) {
            serviceUrl = urlBuilder.createUrl('/tandym/carts/mine/checkout', {});
        } else {
            serviceUrl = urlBuilder.createUrl('/tandym/guest-carts/:quoteId/checkout', {
                quoteId: quote.getQuoteId()
            });
            payload.email = quote.guestEmail;
        }

        return tandymCheckoutService(serviceUrl, payload, messageContainer);
    };
});
