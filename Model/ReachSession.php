<?php

namespace Reach\Payment\Model;

use GuzzleHttp\Client as ReachGuzzleClient;
use OpenAPI\Client\Api\SessionApi;
use OpenAPI\Client\Configuration;
use Reach\Payment\Api\SessionInterface;
use OpenAPI\Client\Model\BillingProfile as ReachBillingProfileModel;
use OpenAPI\Client\Model\Address as ReachAddressModel;
use OpenAPI\Client\Model\Session as ReachClientSessionModel;
use OpenAPI\Client\Model\Item as ReachItemModel;
use Psr\Log\LoggerInterface;

/**
 * ReachSession model
 *
 */
class ReachSession implements SessionInterface
{
    /**
     * @var \Magento\Framework\Session\SessionManager
     */
    protected $session;

    protected $priceHelper;

    protected $countryInformationAcquirer;

    /**
     * @var \Reach\Payment\Api\Data\SessionResponseInterface
     */
    protected $response;

    protected $sessionApi;

    /** @var \Magento\Quote\Api\Data\AddressInterface */
    protected $quoteAddress;

    /** @var \Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId */
    protected $maskedQuoteIdToQuoteId;

    /** @var \Magento\Quote\Api\CartRepositoryInterface $cartRepository */
    protected $cartRepository;

    /** @var \Psr\Log\LoggerInterface $logger */
    protected $logger;

    public function __construct(
        \Magento\Checkout\Model\Session $session,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Magento\Directory\Api\CountryInformationAcquirerInterface $countryInformationAcquirer,
        \Reach\Payment\Api\Data\SessionResponseInterface $response,
        \Magento\Quote\Api\Data\AddressInterface $quoteAddress,
        \Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->session          = $session;
        $this->priceHelper      = $priceHelper;
        $this->countryInformationAcquirer = $countryInformationAcquirer;
        $this->response         = $response;
        $this->quoteAddress     = $quoteAddress;
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
        $this->cartRepository = $cartRepository;
        $this->logger = $logger;

        // ToDo:: Get value from the config setting
        $config = Configuration::getDefaultConfiguration()
            ->setUsername('sdk-team')
            ->setPassword('password');

        $this->sessionApi = new SessionApi(
            new ReachGuzzleClient(),
            $config
        );
    }

    public function generateSessionId($cartId, $guestEmail)
    {
        $quoteId = $this->maskedQuoteIdToQuoteId->execute($cartId);
        $cart = $this->cartRepository->get($quoteId);
        $cartBillingAddress = $cart->getBillingAddress();

        $reachBillingProfile = new ReachBillingProfileModel();
        $reachBillingProfile->setName($cartBillingAddress->getFirstname() . ' ' . $cartBillingAddress->getLastname());

        // Currently retrieving the guest email from the frontend.
        // For some reason the guest email isn't available in the quote (server side)
        $reachBillingProfile->setEmail($guestEmail);

        $country = $this->countryInformationAcquirer->getCountryInfo($cartBillingAddress->getCountryId());

        $reachAddress = new ReachAddressModel();
        $reachAddress->setStreet($cartBillingAddress->getStreetFull());
        $reachAddress->setCity($cartBillingAddress->getCity());
        $reachAddress->setRegion($cartBillingAddress->getRegionCode());
        $reachAddress->setCountry($country->getTwoLetterAbbreviation());
        $reachAddress->setPostcode($cartBillingAddress->getPostcode());

        $reachBillingProfile->setAddress($reachAddress);

        $reachItems = [];

        foreach($cart->getItems() as $cartItem) {
            $reachItem = new ReachItemModel();
            $reachItem->setName($cartItem->getName());
            $reachItem->setAmount($this->priceHelper->currency($cartItem->getPrice(), false));
            $reachItem->setQuantity($cartItem->getQty());

            $reachItems[] = $reachItem;
        }

        $reachSession = new ReachClientSessionModel();
        $reachSession->setMerchantReference(uniqid());
        $reachSession->setCurrency($cart->getCurrency()->getQuoteCurrencyCode());
        $reachSession->setItems($reachItems);
        $reachSession->setBillingProfile($reachBillingProfile);

        // ToDo:: Get value from the config setting
        $reachSession->setAutoCapture(false);

        // ToDo:: Switch these to actual URL's
        $reachSession->setCompleteUrl('https://fe.rch-ext.red/');
        $reachSession->setCancelUrl('https://fe.rch-ext.red/');

        try {
            $result = $this->sessionApi->createSession($reachSession);
            $this->response->setSuccess(true);
            $this->response->setSessionId($result->getSessionId());
        } catch (Exception $e) {
            echo 'Exception when calling SessionApi->createSession: ', $e->getMessage(), PHP_EOL;
        }

        return $this->response;
    }
}
