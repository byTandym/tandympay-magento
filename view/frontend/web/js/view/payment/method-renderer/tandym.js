/**
 * @category    Tandym
 * @package     Tandym_Tandympay
 * @copyright   Copyright (c) Tandym (https://www.bytandym.com/)
 */
define(
    [
        'jquery',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Tandym_Tandympay/js/action/create-tandym-checkout'
    ],
    function (
        $,
        customer,
        Component,
        additionalValidators,
        createTandymCheckoutAction
        ) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Tandym_Tandympay/payment/tandym'
            },

            /**
             * Get Place Order button name
             *
             * @returns string
             */
            getSubmitButtonName: function () {
                //return this.hasCustomerUUID() ? "Place Order" : "Continue to Tandym";
                return "Continue to " + window.checkoutConfig.payment.tandympay.programName;
            },

            /**
             * Get loader message
             *
             * @returns string
             */
            getLoaderMsg: function () {
                //return this.hasCustomerUUID() ? "Placing your order..." : "Redirecting you to Tandym Checkout...";
                return "Redirecting you to "+ window.checkoutConfig.payment.tandympay.programName + " Checkout...";
            },

            /**
             * Get Tandym Image src
             *
             * @returns string
             */
            getTandymImgSrc: function () {
                return window.checkoutConfig.payment.tandympay.logo;
            },

            /**
             * Get Tandym Program Name
             *
             * @returns string
             */
             getTandymProgramName: function () {
                return window.checkoutConfig.payment.tandympay.programName;
            },

            /**
             * Get Tandym Image src
             *
             * @returns string
             */
             getTandymProgramDescription: function () {
                return window.checkoutConfig.payment.tandympay.programDescription;
            },

            /**
             * Handle redirection
             */
            handleRedirectAction: function () {
                var self = this;

                self.isPlaceOrderActionAllowed(false);

                this.getCreateTandymCheckoutDeferredObject()
                    .done(
                        function (response) {
                            var jsonResponse = $.parseJSON(response);
                            $.mage.redirect(jsonResponse.checkout_url);
                            
                        }
                    ).always(
                    function () {
                        self.isPlaceOrderActionAllowed(true);
                    }
                );
            },

            /**
             * Get Create Tandym Checkout Deferred Object
             *
             * @return {*}
             */
            getCreateTandymCheckoutDeferredObject: function () {
                return $.when(
                    createTandymCheckoutAction(this.getData(), this.messageContainer)
                );
            },

            /**
             * Place Order click event
             */
            continueToTandym: function (data, event) {
                if (event) {
                    event.preventDefault();
                }

                if (this.validate()
                    && additionalValidators.validate()
                    && this.isPlaceOrderActionAllowed() === true) {
                    this.handleRedirectAction();
                }
            }
        });
    }
);
