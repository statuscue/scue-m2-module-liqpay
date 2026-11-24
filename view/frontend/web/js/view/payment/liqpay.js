define([
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list',
        'Scue_LiqPay/js/model/config'
], function (Component, rendererList, renderComponentType) {
        'use strict';

        return Component.extend({
            /**
             *  @returns this
             */
            initialize: function () {
                this._super();
                
                rendererList.push(
                    renderComponentType.getComponent(this.connection_types)
                );

                return this;
            },
        });
    }
);
