<?php

namespace Scue\LiqPay\Api;

/**
 * Interface ResponseFieldsInterface
 *
 * @link https://www.liqpay.ua/documentation/en/api/callback
 */
interface ResponseFieldsInterface
{
    const PAYMENT_ID = 'payment_id';
    const ACTION = 'action';
    const STATUS = 'status';
    const VERSION = 'version';
    const TYPE = 'type';
    const PAY_TYPE = 'paytype';
    const ACQUIRER_ID = 'acq_id';
    const ORDER_ID = 'order_id';
    const LIQPAY_ORDER_ID = 'liqpay_order_id';
    const SENDER_CARD_MASK = 'sender_card_mask2';
    const SENDER_CARD_BANK = 'sender_card_bank';
    const SENDER_CARD_TYPE = 'sender_card_type';
    const SENDER_CARD_COUNTRY = 'sender_card_country';
    const CREATE_DATE = 'create_date';
    const END_DATE = 'end_date';
    const TRANSACTION_ID = 'transaction_id';
}
