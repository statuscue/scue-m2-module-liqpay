define([], function () {
    return {
        /**
         * @return {component: string, type: string}
         */
        getComponent: function (connectionTypes) {
            const connectionType = this.getConnectionType();

            return {
                type: 'scue_liqpay',
                component: connectionTypes[connectionType]
            };
        },

        /**
         * @return string
         */
        getConnectionType: function () {
            return window.checkoutConfig.payment.scue_liqpay.connection_type;
        }
    };
});
