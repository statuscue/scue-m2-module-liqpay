<?php

namespace Scue\LiqPay\Api;

interface LiqPayCheckoutCallbackInterface
{
    /**
     * Process the callback from LiqPay.
     * @return void
     */
    public function callback();
}
