<?php

namespace Reach\Payment\Gateway\Response;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;
use Psr\Log\LoggerInterface;

class TxnIdHandler implements HandlerInterface
{
    const TXN_ID = 'TXN_ID';

    protected $logger;

    public function __construct(
        LoggerInterface $logger
    )
    {
        $this->logger = $logger;
    }

    /**
     * Handles transaction id
     *
     * @param array $handlingSubject
     * @param array $response
     * @return void
     */
    public function handle(array $handlingSubject, array $response)
    {
        if (!isset($handlingSubject['payment'])
            || !$handlingSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

        $this->logger->debug("************************ MAG-85-POC - TXN HANDLER", [
            'TXN_ID' => $response[self::TXN_ID]
        ]);

        /** @var PaymentDataObjectInterface $paymentDO */
        $paymentDO = $handlingSubject['payment'];

        $payment = $paymentDO->getPayment();

        // Set transaction to pending by default, wait for notification.
        /** @var Payment $payment  */
        $payment->setIsTransactionPending(true);

        /** @var $payment Payment */
        $payment->setTransactionId($response[self::TXN_ID]);
        $payment->setIsTransactionClosed(false);
    }
}