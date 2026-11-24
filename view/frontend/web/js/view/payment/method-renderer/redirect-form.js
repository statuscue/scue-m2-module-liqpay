define([
    'Magento_Checkout/js/view/payment/default',
    'Magento_Customer/js/customer-data',
    'Magento_Checkout/js/model/error-processor',
    'Magento_Checkout/js/model/full-screen-loader',
    'jquery',
    'mage/url',
    'Scue_LiqPay/js/form-builder'
], function (Component, customerData, errorProcessor, fullScreenLoader, $, url, formBuilder) {
    'use strict';

    return Component.extend({
        redirectAfterPlaceOrder: false, // This is important, so the customer isn't redirected to success.phtml by default
        defaults: {
            template: 'Scue_LiqPay/payment/redirect-form',
            code: 'scue_liqpay'
        },

        /**
         * @returns {String}
         */
        getCode: function () {
            return this.code;
        },

        /**
         * @returns {Boolean}
         */
        isActive: function () {
            return this.getCode() === this.isChecked();
        },

        /**
         * @param {String} field
         * @returns {String}
         */
        getSelector: function (field) {
            return '#' + this.getCode() + '_' + field;
        },

        afterPlaceOrder: function () {
            let customControllerUrl = url.build('liqpay/checkout/start');
            $.ajax({
                type: "POST",
                url: customControllerUrl,
                dataType: 'json'
            }).done(function (response) {
                console.log(['scue.liqpay.controller.start.response', response, this.messsageContainer]);
                if (response.status === 'success') {
                    customerData.invalidate(['cart']);
                    formBuilder(response).submit();
                } else {
                    // TODO: show error message and redirect
                    if (response.redirect) {
                        window.location = response.redirect;
                    }
                }
            }).fail(function (response) {
                errorProcessor.process(response, this.messageContainer);
            }).always(function () {
                fullScreenLoader.stopLoader();
            });
        }
    })
});
