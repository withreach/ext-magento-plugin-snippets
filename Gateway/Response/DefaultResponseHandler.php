<?php
namespace Reach\Payment\Gateway\Response;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;
use Psr\Log\LoggerInterface;

class DefaultResponseHandler implements HandlerInterface
{
    protected $logger;

    public function __construct(
        LoggerInterface $logger
    )
    {
        $this->logger = $logger;
    }

    /**
     * Handles fraud messages
     *
     * @param array $handlingSubject
     * @param array $response
     * @return void
     */
    public function handle(array $handlingSubject, array $response)
    {
        $this->logger->debug(
            '**************** MAG-85-POC - RESPONSE HANDLER',
            [
                'increment_id' => $response['increment_id'],
            ]
        );

        if (!isset($handlingSubject['payment'])
            || !$handlingSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

        /** @var PaymentDataObjectInterface $paymentDO */
        $paymentDO = $handlingSubject['payment'];
        $payment = $paymentDO->getPayment();

        // Set transaction to pending by default, wait for notification.
        /** @var Payment $payment  */
        $payment->setIsTransactionPending(true);

        // do not close transaction so you can do a cancel() and void
        $payment->setIsTransactionClosed(false);
        $payment->setShouldCloseParentTransaction(false);
    }
}
