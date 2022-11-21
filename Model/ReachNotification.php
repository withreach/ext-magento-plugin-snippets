<?php

namespace Reach\Payment\Model;

use GuzzleHttp\Client as ReachGuzzleClient;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Model\Order\Payment\Transaction\Builder;
use OpenAPI\Client\Api\OrderApi;
use OpenAPI\Client\Configuration;
use Reach\Payment\Api\Data\ResponseInterface;
use Reach\Payment\Api\NotificationInterface;
use Magento\Framework\Webapi\Exception;
use Magento\Framework\Phrase;
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Webapi\Rest\Request;
use Reach\Payment\Api\ReachSessionRelationRepositoryInterface;
use OpenAPI\Client\Api\OrderApi as ReachClientOpenApi;
use Magento\Sales\Model\Order;

/**
 * ReachNotification model
 *
 */
class ReachNotification implements NotificationInterface
{
    /** @var ResponseInterface */
    protected $response;

    /**
     * @var \Reach\Payment\Helper\Data
     */
    protected $reachHelper;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var TransactionRepositoryInterface
     */
    protected $transactionRepository;

    /**
     * @var LoggerInterface $logger
     */
    protected $logger;

    /** @var Request $requestInterface */
    protected $requestInterface;

    /** @var ReachSessionRelationRepositoryInterface $reachSessionRelationRepository */
    protected $reachSessionRelationRepository;

    /**
     * @var Builder
     */
    protected $transactionBuilder;

    public function __construct(
        ResponseInterface                 $response,
        \Reach\Payment\Helper\Data        $reachHelper,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        OrderRepositoryInterface          $orderRepository,
        TransactionRepositoryInterface    $transactionRepository,
        LoggerInterface $logger,
        Request $requestInterface,
        ReachSessionRelationRepositoryInterface $reachSessionRelationRepository,
        Builder $transactionBuilder
    )
    {
        $this->response = $response;
        $this->reachHelper = $reachHelper;
        $this->_orderFactory = $orderFactory;
        $this->orderRepository = $orderRepository;
        $this->transactionRepository = $transactionRepository;
        $this->logger = $logger;
        $this->requestInterface = $requestInterface;
        $this->reachSessionRelationRepository = $reachSessionRelationRepository;
        $this->transactionBuilder = $transactionBuilder;
    }

    /**
     * @return ResponseInterface
     */
    public function handleNotification()
    {
        $request = $this->requestInterface->getContent();
        $signature = $this->requestInterface->getHeader('reach-signature');

        $this->logger->debug('************************************************************************* MAG-85-POC - MADE IT TO THE NOTIFICATION',
        [
            'request_body' => $this->requestInterface->getBodyParams()
        ]);

        try {
            if ($this->validate($request, $signature)) {
                $bodyParams = $this->requestInterface->getBodyParams();

                // ToDo:: Get value from the config setting
                $config = Configuration::getDefaultConfiguration()
                    ->setUsername('sdk-team')
                    ->setPassword('password');

                $reachOrderApi = new OrderApi(
                    new ReachGuzzleClient(),
                    $config
                );

                // Makes a request to the Order API
                $reachOrder = $reachOrderApi->retrieveOrder($bodyParams['Order']['OrderId']);

                // Finds the matching Magento Order from the database
                $order = $this->loadOrder($reachOrder->getMeta()['magento_increment_id']);

                $paymentObj = $order->getPayment();
                $paymentObj->setAdditionalInformation([
                    'reach_order_id' => $reachOrder->getOrderId()
                ]);

                $eventType = $bodyParams['EventType'];

                switch ($eventType) {
                    case 'ORDER_AUTHORIZED':
                        // Would only be required if its a CC
                        $paymentObj->setCcTransId($reachOrder->getOrderId());
                        $paymentObj->setLastTransId($reachOrder->getOrderId());

                        // TODO:: Properly map the Reach CC code to the Magneto ccTypes
                        $paymentObj->setCcType('VI');

                        // set transaction
                        $paymentObj->setTransactionId($reachOrder->getOrderId());

                        if ($reachOrder->getPayment()) {
                            $paymentObj->setCcLast4($reachOrder->getPayment()->getCard()->getLastFour());
                        }

                        // Prepare transaction
                        $transaction = $this->transactionBuilder->setPayment($paymentObj)
                            ->setOrder($order)
                            ->setTransactionId($reachOrder->getOrderId())
                            ->build(TransactionInterface::TYPE_AUTH);

                        $transaction->setIsClosed(false);
                        $order->setState(Order::STATE_PROCESSING);
                        $order->setStatus(Order::STATE_PROCESSING);
                        $order->addStatusToHistory($order->getStatus(), 'Order was successfully authorized by Reach');


                        $this->transactionRepository->save($transaction);
                        $this->orderRepository->save($order);

                        $this->logger->debug('MAG-85-POC - ORDER AUTHORIZED NOTIFICATION COMPLETE');
                        break;
                    case 'ORDER_PROCESSED':
                        $order->setState(Order::STATE_PROCESSING);
                        $order->setStatus(Order::STATE_PROCESSING);
                        $order->addStatusToHistory($order->getStatus(), 'Order was successfully captured by Reach');

                        break;
                    default:
                        break;
                }

//                $reachSessionRelation = $this->reachSessionRelationRepository->getBySessionId($bodyParams['Order']['SessionId']);
//
//                $this->logger->debug('************************************************************************* MAG-85-POC - Reach Session Relation', [
//                    'reach_session_relation' => $reachSessionRelation->getReachOrderId()
//                ]);
//
//                // Stores the Reach order ID
//                if (!is_null($reachSessionRelation->getReachOrderId())) {
//                    $reachSessionRelation->setReachOrderId($reachOrder->getMerchantReference());
//                    $this->reachSessionRelationRepository->save($reachSessionRelation);
//                }
            } else {
                $this->logger->debug('************************************************************************* MAG-85-POC - INVALID NOTIFICATION',
                    [
                        'request_body' => $this->requestInterface->getBodyParams()
                    ]);
            }
        } catch (\Exception $e) {
            throw new Exception(new Phrase($e->getMessage()));
        }

        return $this->response;
    }

    private function validate($request, $nonce)
    {
        $key = $this->reachHelper->getSecret();
        $signature = base64_encode(hash_hmac('sha256', $request, $key, true));
        return $signature == $nonce;
    }

    private function loadOrder(string $incrementId)
    {
        $order = $this->_order = $this->_orderFactory->create()->loadByIncrementId($incrementId);

        if ($order === null || $order->getId() === null) {
            throw new \Magento\Framework\Exception\LocalizedException(__("Invalid order."));
        }
        return $order;
    }
}
