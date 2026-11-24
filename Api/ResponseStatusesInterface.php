<?php

namespace Scue\LiqPay\Api;

/**
 * Interface ResponseFieldsInterface
 * 
 * @link https://www.liqpay.ua/documentation/en/api/callback
 */
interface ResponseStatusesInterface
{
    /**#@+
     * Final payment statuses
     */
    const ERROR = 'error';
    const FAILURE = 'failure';
    const REVERSED = 'reversed';
    const SUBSCRIBED = 'subscribed';
    const SUCCESS = 'success';
    const UNSUBSCRIBED = 'unsubscribed';
    /**#@-*/
    
    
    
}
