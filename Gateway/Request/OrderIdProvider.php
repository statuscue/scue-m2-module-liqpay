<?php

declare(strict_types=1);

namespace Scue\LiqPay\Gateway\Request;

use Scue\LiqPay\Gateway\Config;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Class OrderIdProvider.
 */
class OrderIdProvider
{
    private Config $config;

    /**
     * OrderIdProvider constructor.
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @param OrderInterface $order
     * @return string
     */
    public function get(OrderInterface $order)
    {
        return $this->config->getOrderPrefix() . $order->getIncrementId() . $this->config->getOrderSuffix();
    }
}
