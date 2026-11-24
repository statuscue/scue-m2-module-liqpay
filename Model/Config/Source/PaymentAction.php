<?php

declare(strict_types=1);

namespace Scue\LiqPay\Model\Config\Source;

use Scue\LiqPay\Api\Data\PaymentActionInterface;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class PaymentAction
 */
class PaymentAction implements OptionSourceInterface
{
    /**
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        return [
            ['value' => PaymentActionInterface::AUTHORIZE, 'label' => __('Authorize Only')],
            ['value' => PaymentActionInterface::AUTHORIZE_CAPTURE, 'label' => __('Authorize and Capture')],
        ];
    }
}
