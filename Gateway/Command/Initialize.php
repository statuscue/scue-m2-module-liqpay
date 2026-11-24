<?php

declare(strict_types=1);

namespace Scue\LiqPay\Gateway\Command;

use Magento\Payment\Gateway\Command;
use Magento\Payment\Gateway\Command\CommandException;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Sales\Model\Order;

/**
 * Initialize payment method before placing order
 *
 * @package Scue\LiqPay\Gateway\Command
 */
class Initialize implements CommandInterface
{
    /**
     * @inheritDoc
     */
    public function execute(array $commandSubject)
    {
        /** @var \Magento\Payment\Gateway\Data\PaymentDataObject $payment */
        $payment = $commandSubject['payment'];
        /** @var \Magento\Framework\DataObject $stateObject */
        $stateObject = $commandSubject['stateObject'];

        $stateObject->setState(Order::STATE_PENDING_PAYMENT);
        $stateObject->setStatus(Order::STATE_PENDING_PAYMENT);
        $stateObject->setIsNotified(false);
    }
}
