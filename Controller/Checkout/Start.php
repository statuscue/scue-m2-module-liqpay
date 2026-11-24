<?php

declare(strict_types=1);

namespace Scue\LiqPay\Controller\Checkout;

use Scue\LiqPay\Api\Data\ApiUrlInterface;
use Scue\LiqPay\Api\Data\PaymentMethodCodeInterface;
use Scue\LiqPay\Api\Data\VersionInterface;
use Scue\LiqPay\Gateway\Config;
use Scue\LiqPay\Gateway\Request\OrderIdProvider;
use Scue\LiqPay\Gateway\Request\PaymentActionProvider;
use Scue\LiqPay\Sdk\LiqPay as LiqPaySdk;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory as JsonResultFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\UrlInterface;

class Start implements HttpPostActionInterface
{
    /**
     * @var JsonResultFactory
     */
    private JsonResultFactory $resultJsonFactory;
    /**
     * @var Config
     */
    private Config $config;
    /**
     * @var UrlInterface
     */
    private UrlInterface $url;
    /**
     * @var ManagerInterface
     */
    private ManagerInterface $messageManager;
    /**
     * @var CheckoutSession
     */
    private CheckoutSession $checkoutSession;
    /**
     * @var LiqPaySdk
     */
    private LiqPaySdk $liqPaySdk;
    /**
     * @var OrderIdProvider
     */
    private OrderIdProvider $orderIdProvider;
    /**
     * @var PaymentActionProvider
     */
    private PaymentActionProvider $paymentActionProvider;

    public function __construct(
        JsonResultFactory $resultJsonFactory,
        Config $config,
        UrlInterface $url,
        ManagerInterface $messageManager,
        CheckoutSession $checkoutSession,
        OrderIdProvider $orderIdProvider,
        PaymentActionProvider $paymentActionProvider,
        LiqPaySdk $liqPaySdk
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->config = $config;
        $this->url = $url;
        $this->messageManager = $messageManager;
        $this->checkoutSession = $checkoutSession;
        $this->liqPaySdk = $liqPaySdk;
        $this->orderIdProvider = $orderIdProvider;
        $this->paymentActionProvider = $paymentActionProvider;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        // Note, that controller may be accessed directly.
        try {
            if (!$this->config->isEnabled()) {
                throw new \Exception(__('Payment is not allowed.'));
            }

            if (!($this->config->getPublicKey() && $this->config->getPrivateKey())) {
                throw new \Exception(__('Payment method turned off, because public or private key is not set.'));
            }

            // Make sure that we really have an order
            $order = $this->checkoutSession->getLastRealOrder();
            if (!($order && $order->getId())) {
                throw new \Exception(__('Order not found.'));
            }

            // Verify payment method
            $method = $order->getPayment()->getMethod();
            if ($method !== PaymentMethodCodeInterface::CODE) {
                throw new \Exception('Order payment method is not a LiqPay');
            }

            $params = [
                'action' => $this->paymentActionProvider->getPaymentAction(),
                'amount' => $order->getGrandTotal(),
                'currency' => $order->getOrderCurrencyCode(),
                'description' => 'LiqPay for Magento 2', // TODO: get description
                'order_id' => $this->orderIdProvider->get($order),
                'version' => VersionInterface::VERSION,
                'language' => 'ru', // TODO: set correct user language
                'server_url' => $this->url->getUrl('rest/V1/liqpay/checkout/callback')
            ];

            $form = $this->liqPaySdk->cnb_form_raw($params);

            $data = [
                'status' => 'success',
                'action' => sprintf('%s/%s/%s', rtrim(ApiUrlInterface::API_URL, '/\\'), VersionInterface::VERSION, ApiUrlInterface::REQUEST_ENDPOINT_PATH),
                'fields' => [
                    'data' => $form['data'],
                    'signature' => $form['signature'],
                ],
            ];
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong, please try again later'));
            // TODO: log exception
            // $this->logger->critical($e);
            $this->checkoutSession->restoreQuote();
            $data = [
                'status' => 'error',
                'redirect' => $this->url->getUrl('checkout/cart'),
            ];
        }

        $result = $this->resultJsonFactory->create();
        $result->setData($data);

        return $result;
    }
}
