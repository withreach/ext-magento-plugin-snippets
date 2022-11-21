<?php

namespace Reach\Payment\Gateway\Request;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use OpenAPI\Client\Model\Address as ReachAddressModel;
use OpenAPI\Client\Model\BillingProfile as ReachBillingProfileModel;
use OpenAPI\Client\Model\Item as ReachItemModel;
use OpenAPI\Client\Model\Session as ReachClientSessionModel;
use Psr\Log\LoggerInterface;

class ReachSessionRequest implements BuilderInterface
{
    /**
     * @var \Magento\Directory\Api\CountryInformationAcquirerInterface $countryInformationAcquirer
     */
    protected $countryInformationAcquirer;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data $priceHelper
     */
    protected $priceHelper;

    protected $logger;

    /**
     * @param ConfigInterface $config
     */
    public function __construct(
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Magento\Directory\Api\CountryInformationAcquirerInterface $countryInformationAcquirer,
        LoggerInterface $logger
    ) {
        $this->priceHelper = $priceHelper;
        $this->countryInformationAcquirer = $countryInformationAcquirer;
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

        /** @var PaymentDataObjectInterface $payment */
        $payment = $buildSubject['payment'];
        $order = $payment->getOrder();
        $address = $order->getBillingAddress();
        $sessionId = $payment->getPayment()->getAdditionalInformation('session_id');

        $reachBillingProfile = new ReachBillingProfileModel();
        $reachBillingProfile->setName($address->getFirstname() . ' ' . $address->getLastname());
        $reachBillingProfile->setEmail($address->getEmail());

        $country = $this->countryInformationAcquirer->getCountryInfo($address->getCountryId());

        $reachAddress = new ReachAddressModel();
        // TODO:: Merge both street lines
        $reachAddress->setStreet($address->getStreetLine1());
        $reachAddress->setCity($address->getCity());
        $reachAddress->setRegion($address->getRegionCode());
        $reachAddress->setCountry($country->getTwoLetterAbbreviation());
        $reachAddress->setPostcode($address->getPostcode());

        $reachBillingProfile->setAddress($reachAddress);

        $reachItems = [];

        /** @var \Magento\Sales\Model\Order\Item $item */
        foreach($order->getItems() as $item) {
            $reachItem = new ReachItemModel();
            $reachItem->setName($item->getName());
            $reachItem->setAmount($this->priceHelper->currency($item->getPrice(), false));
            $reachItem->setQuantity($item->getQtyOrdered());

            $reachItems[] = $reachItem;
        }

        $reachSession = new ReachClientSessionModel();
        // Update the MerchantReference to the Order increment_id
        $reachSession->setMerchantReference($this->getUUID());
        $reachSession->setCurrency($order->getCurrencyCode());
        $reachSession->setItems($reachItems);
        $reachSession->setBillingProfile($reachBillingProfile);

        // ToDo:: Get value from the config setting
        $reachSession->setAutoCapture(false);

        $meta = (object) [
            'magento_increment_id' => $order->getOrderIncrementId()
        ];

        $reachSession->setMeta($meta);

        // ToDo:: Switch these to actual URL's
        $reachSession->setCompleteUrl('https://fe.rch-ext.red/');
        $reachSession->setCancelUrl('https://fe.rch-ext.red/');

        return [
            'TXN_TYPE' => 'A',
            'INVOICE' => $order->getOrderIncrementId(),
            'AMOUNT' => $order->getGrandTotalAmount(),
            'CURRENCY' => $order->getCurrencyCode(),
            'EMAIL' => $address->getEmail(),
            'REACH_SESSION_ID' => $sessionId,
            'REACH_SESSION_MODEL' => $reachSession
        ];
    }

    /**
     * Generate a UUID
     *
     * Copied from @see http://guid.us/GUID/PHP
     *
     * @return string
     */
    private static function getUUID()
    {
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $hyphen = chr(45);// "-"
        $uuid = substr($charid, 0, 8) . $hyphen
            . substr($charid, 8, 4) . $hyphen
            . substr($charid, 12, 4) . $hyphen
            . substr($charid, 16, 4) . $hyphen
            . substr($charid, 20, 12);
        return $uuid;
    }

}