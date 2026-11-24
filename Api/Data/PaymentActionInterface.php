<?php

namespace Scue\LiqPay\Api\Data;

/**
 * Interface PaymentActionInterface
 * @api
 */
interface PaymentActionInterface
{
    const AUTHORIZE = 'authorize';
    const AUTHORIZE_CAPTURE = 'authorize_capture';
    const REFUND = 'refund';
}
