<?php

declare(strict_types=1);

namespace Scue\LiqPay\Sdk;

use Scue\LiqPay\Gateway\Config;

class LiqPay extends \LiqPay
{
    /**
     * @var Config
     */
    private Config $config;

    public function __construct(
        Config $config
    ) {
        $this->config = $config;

        if ($this->config->isEnabled()) {
            $publicKey = $this->config->getPublicKey();
            $privateKey = $this->config->getPrivateKey();
            parent::__construct($publicKey, $privateKey);
        }
    }
}
