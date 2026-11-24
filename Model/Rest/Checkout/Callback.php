<?php

declare(strict_types=1);

namespace Scue\LiqPay\Model\Rest\Checkout;

use Scue\LiqPay\Api\Data\PaymentMethodCodeInterface;
use Scue\LiqPay\Api\LiqPayCheckoutCallbackInterface;
use Scue\LiqPay\Api\ResponseFieldsInterface;
use Scue\LiqPay\Api\ResponseStatusesInterface as Status;
use Scue\LiqPay\Gateway\Config;
use Scue\LiqPay\Gateway\Converter\JsonToArray as Converter;
use Scue\LiqPay\Gateway\Request\SignatureFactory;
use Scue\LiqPay\Gateway\Response\Decoder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DB\TransactionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface as PaymentTransactionBuilder;
use Magento\Sales\Model\Service\InvoiceService;
use Psr\Log\LoggerInterface;

class Callback implements LiqPayCheckoutCallbackInterface
{
    private RequestInterface $request;
    private Order $order;
    private OrderRepositoryInterface $orderRepository;
    private InvoiceService $invoiceService;
    private InvoiceSender $invoiceSender;
    private PaymentTransactionBuilder $paymentTransactionBuilder;
    private TransactionFactory $transactionFactory;
    private Converter $converter;
    private Decoder $decoder;
    private LoggerInterface $logger;
    private Config $config;
    private SignatureFactory $signatureFactory;

    /**
     * Callback constructor.
     * @param RequestInterface $request
     * @param Config $config
     * @param Order $order
     * @param OrderRepositoryInterface $orderRepository
     * @param InvoiceService $invoiceService
     * @param InvoiceSender $invoiceSender
     * @param PaymentTransactionBuilder $paymentTransactionBuilder
     * @param TransactionFactory $transactionFactory
     * @param Converter $converter
     * @param Decoder $decoder
     * @param SignatureFactory $signatureFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        RequestInterface $request,
        Config $config,
        Order $order,
        OrderRepositoryInterface $orderRepository,
        InvoiceService $invoiceService,
        InvoiceSender $invoiceSender,
        PaymentTransactionBuilder $paymentTransactionBuilder,
        TransactionFactory $transactionFactory,
        Converter $converter,
        Decoder $decoder,
        SignatureFactory $signatureFactory,
        LoggerInterface $logger
    ) {
        $this->request = $request;
        $this->order = $order;
        $this->orderRepository = $orderRepository;
        $this->invoiceService = $invoiceService;
        $this->transactionFactory = $transactionFactory;
        $this->converter = $converter;
        $this->decoder = $decoder;
        $this->logger = $logger;
        $this->config = $config;
        $this->signatureFactory = $signatureFactory;
        $this->paymentTransactionBuilder = $paymentTransactionBuilder;
        $this->invoiceSender = $invoiceSender;
    }

    /**
     * @inheritDoc
     */
    public function callback()
    {
        $params = $this->request->getParams();
        if (!(isset($params['signature']) && isset($params['data']))) {
            $this->logger->error('Missing "data" or "signature" parameters in a response from LiqPay.');
            return;
        }

        $data = $this->converter->convert($this->decoder->decode($params['data']));

        $orderId = $data['order_id'] ?? null;
        $order = $this->order->loadByIncrementId($orderId);
        if (!$order->getId()) {
            $this->logger->error("The order #{$orderId} no longer exists.");
            return;
        }

        $payment = $order->getPayment();
        if (!$this->verifyPaymentMethod($payment)) {
            $this->logger->error("Payment method mismatch for order #{$orderId}.");
            $this->logger->debug("Payment method for order #{$orderId} is `{$payment->getMethod()}`");
            return;
        }

        if (!$this->verifySignature($params['signature'], $params['data'])) {
            $this->logger->error("Signature verification failed for order #{$orderId}");

            $order->addCommentToStatusHistory(__('LiqPay security check failed.'));
            $this->orderRepository->save($order);
            return;
        }

        try {
            $state = null;
            $status = $data['status'] ?? null;
            switch ($status) {
                case Status::SUCCESS:
                    if (!$order->canInvoice()) {
                        throw new LocalizedException(__('The order #%1 does not allow an invoice to be created.', $orderId));
                    }

                    $state = Order::STATE_PROCESSING;
                    $this->createInvoice($order);
                    $this->createTransaction($order, $data);
                    break;
                case Status::FAILURE:
                    $state = Order::STATE_CANCELED;
                    $order->addCommentToStatusHistory(__('Failed payment.'));
                    break;
                case Status::ERROR:
                    $state = Order::STATE_CANCELED;
                    $order->addCommentToStatusHistory(__('Failed payment. Data is incorrect.'));
                    break;
                default:
                    $order->addCommentToStatusHistory(__('Unexpected status from LiqPay server: %1.', $status));
                    break;
            }

            if ($state) {
                $order->setState($state);
                $order->setStatus($state);
            }

            $this->orderRepository->save($order);
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }

    /**
     * @param OrderPaymentInterface $payment
     * @return bool
     */
    protected function verifyPaymentMethod(OrderPaymentInterface $payment): bool
    {
        return $payment->getMethod() === PaymentMethodCodeInterface::CODE;
    }

    /**
     * Verify signature.
     *
     * According to documentation we must use the `data` received in response from LiqPay.
     * @link https://www.liqpay.ua/documentation/en/api/callback
     *
     * @param string $signature
     * @param string $data
     * @return bool
     */
    protected function verifySignature(string $signature, string $data): bool
    {
        return $signature === $this->signatureFactory->create($data);
    }

    /**
     * @param Order $order
     * @throws LocalizedException
     */
    protected function createInvoice(Order $order)
    {
        $orderId = $order->getIncrementId();
        $invoice = $this->invoiceService->prepareInvoice($order);
        if (!$invoice) {
            throw new LocalizedException(__('We can\'t save the invoice right now for order #%1', $orderId));
        }

        if (!$invoice->getTotalQty()) {
            throw new LocalizedException(__('You can\'t create an invoice for order #%1 without products.', $orderId));
        }

        $invoice->register();
        $invoice->pay();

        $transaction = $this->transactionFactory->create();
        $transaction->addObject($invoice);
        $transaction->addObject($invoice->getOrder());
        $transaction->save();

        $this->invoiceSender->send($invoice);

        $order->addCommentToStatusHistory(__('Invoice #%1 created.', $invoice->getIncrementId()));
    }

    /**
     * @param Order $order
     * @param array $paymentData
     * @throws LocalizedException
     */
    protected function createTransaction(Order $order, array $paymentData)
    {
        if (!isset($paymentData[ResponseFieldsInterface::TRANSACTION_ID])) {
            throw new LocalizedException(
                __(
                    'Can\'t create a transaction for order #%1 - %2 field is missing in response.',
                    $order->getIncrementId(),
                    ResponseFieldsInterface::TRANSACTION_ID
                )
            );
        }

        $transactionId = $paymentData[ResponseFieldsInterface::TRANSACTION_ID];

        $payment = $order->getPayment();
        $payment->setLastTransId($transactionId);
        $payment->setTransactionId($transactionId);
        $payment->setAdditionalInformation([Transaction::RAW_DETAILS => $paymentData]);
        $payment->setParentTransactionId(null);
        $payment->save();

        $transaction = $this->paymentTransactionBuilder
            ->setPayment($payment)
            ->setOrder($order)
            ->setTransactionId($transactionId)
            ->setAdditionalInformation([Transaction::RAW_DETAILS => $paymentData])
            ->setFailSafe(true)
            ->build(Transaction::TYPE_CAPTURE);
        $transaction->save();

        $order->addCommentToStatusHistory(__('Transaction #%1 created.', $transactionId));
    }
}
