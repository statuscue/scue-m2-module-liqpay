<?php

declare(strict_types = 1);

namespace Scue\LiqPay\Model\Config\Source;

use Scue\LiqPay\Api\Data\EnvironmentStatusInterface;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Environment
 */
class Environment implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => EnvironmentStatusInterface::PRODUCTION, 'label' => __('Production')],
            ['value' => EnvironmentStatusInterface::SANDBOX, 'label' => __('Sandbox')],
        ];
    }
}
