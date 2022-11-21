<?php

namespace Reach\Payment\Gateway\Request;

use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Psr\Log\LoggerInterface;

class CaptureRequest implements BuilderInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    protected $logger;

    /**
     * @param ConfigInterface $config
     */
    public function __construct(
        ConfigInterface $config,
        LoggerInterface $logger
    ) {
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        if (!isset($buildSubject['payment'])
            || !$buildSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

        /** @var PaymentDataObjectInterface $paymentDO */
        $paymentDO = $buildSubject['payment'];

        $order = $paymentDO->getOrder();

        $payment = $paymentDO->getPayment();

        if (!$payment instanceof OrderPaymentInterface) {
            throw new \LogicException('Order payment should be provided.');
        }

        $this->logger->debug("************************ MAG-85-POC - CAPTURE REQUEST", [
            'TXN_ID' => $payment->getLastTransId()
        ]);

        return [
            'TXN_TYPE' => 'S',
            'TXN_ID' => $payment->getLastTransId(),
            'MERCHANT_KEY' => $this->config->getValue(
                'merchant_gateway_key',
                $order->getStoreId()
            )
        ];
    }
}