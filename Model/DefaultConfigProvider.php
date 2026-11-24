<?php

declare(strict_types=1);

namespace Scue\LiqPay\Model;

use Scue\LiqPay\Api\Data\PaymentMethodCodeInterface;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Payment\Gateway\ConfigInterface;

/**
 * Class DefaultConfigProvider
 */
class DefaultConfigProvider implements ConfigProviderInterface
{
    private ConfigInterface $config;

    /**
     * DefaultConfigProvider constructor.
     * @param ConfigInterface $config
     */
    public function __construct(
        ConfigInterface $config
    ) {
        $this->config = $config;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig(): array
    {
        return [
            'payment' => [
                PaymentMethodCodeInterface::CODE => [
                    'connection_type' => $this->config->getValue('connection_type')
                ]
            ]
        ];
    }
}
