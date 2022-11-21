<?php


namespace Reach\Payment\Test\Unit\Model;


use Magento\Payment\Model\Method\ConfigInterfaceFactory;
use Reach\Payment\Model\Reach;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

use Magento\Framework\Session\SessionManagerInterface;
use Reach\Payment\Model\Currency;
use Reach\Payment\Helper\Data;
use Magento\Checkout\Model\Session;
use Reach\Payment\Model\Api\HttpRestFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManager;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\Store;
use Magento\Payment\Model\InfoInterface;

class MiscTest extends TestCase
{
    /**
     * @var Reach\Payment\Model\Cc
     */
    private $cc;
    /**
     * @var Reach MockObject
     */
    private $reachPayment;

    /**
     * @var  SessionManagerInterface MockObject
     */

    protected $_coresession;

    /**
     * @var  Currency MockObject
     */
    protected $reachCurrency;

    /**
     * @var Data MockObject
     */
    protected $reachHelper;

    /**
     * @var Session MockObject
     */
    protected $checkoutSession;

    /**
     * @var HttpRestFactory MockObject
     */
    protected $httpRestFactory;

    /**
     * @var HttpTextFactory MockObject
     */
    protected $HttpTextFactory;

    /**
     * @var bool
     */

    private $example = true;

    /**
     * @var string
     */


    const PATH = 'payment/reach_cc/active';


    /**
     * @var ScopeConfigInterface MockObject
     */
    protected $scopeConfig;

    /**
     *  @var StoreManager MockObject
     */
    protected $storeManager;

    /**
     * @var Store MockObject
     */
      protected $store;
    /**
     * @var UrlInterface MockObject
     */

    protected $coreUrl;

    /**
    @var \Magento\Payment\Model\Method\AbstractMethod  MockObject
     */
    protected $ccMethodMock;

    /**
     * @var \Magento\Sales\Model\Order\Payment MockObject
     */
    protected $payment;

    /**
     * @var \Magento\Sales\Model\Order MockObject

     */
    protected $order;


    /**
     * @var \Reach\Payment\Api\Data\HttpResponseInterface MockObject
     *
     */
    protected $httpResponseMock;

    /**
     * @var \Reach\Payment\Model\Api\Http  MockObject
     *
     */
    protected $http;

    /**
     * @var \Reach\Payment\Model\Api\HttpText MockObject
     */
    protected $rest;

    /**
     * @var \Reach\Payment\Helper\CcHelper MockObject
     */
    protected $ccHelper;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->_coresession = $this->getMockBuilder('Magento\Framework\Session\SessionManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->checkoutSession = $this->getMockBuilder('Magento\Checkout\Model\Session')
            ->disableOriginalConstructor()
            ->getMock();
        $this->httpRestFactory = $this->getMockBuilder('Reach\Payment\Model\Api\HttpRestFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $this->httpResponseMock = $this->getMockBuilder('Reach\Payment\Api\Data\HttpResponseInterface')
             ->disableOriginalConstructor()
             ->getMock();
        $this->http = $this->getMockBuilder('Reach\Payment\Model\Api\Http')
            ->disableOriginalConstructor()
            ->getMock();
        $body = [];
        $this->http->method('executePost')->with($body)->willReturn($this->httpResponseMock );
       /*
        $this->httpText = $this->getMockBuilder('Reach\Payment\Model\Api\HttpText')
            ->disableOriginalConstructor()
            ->setMethods(['processResponse'])
            ->getMock();

        $httpText = $this->httpText->method('processResponse')->willReturn([]);

        $this->rest = $this->httpTextFactory->method('create')->willReturn();

        $this->httpTextFactory = $this->getMockBuilder('Reach\Payment\Model\Api\HttpTextFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create()'])
            ->getMock();
        //$this->httpTextFactory->method('create')->willReturn()
 */
        $this->reachCurrency = $this->getMockBuilder('Reach\Payment\Model\Currency')
            ->disableOriginalConstructor()
            ->getMock();
        $this->reachHelper = $this->getMockBuilder('Reach\Payment\Helper\Data')
            ->disableOriginalConstructor()
            ->setMethods(['getReachEnabled', 'getCreditCardActive', 'isAvailable', 'getSecret','getCheckOutUrl'])
            ->getMock();
        $this->reachPayment = $this->getMockBuilder('Reach\Payment\Model\Reach')
            ->disableOriginalConstructor()
            ->getMock();
        $this->store = $this->getMockBuilder('Magento\Store\Model\Store')
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager = $this->getMockBuilder('Magento\Store\Model\StoreManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->order =  $this->getMockBuilder('Magento\Sales\Model\Order')
            ->disableOriginalConstructor()
            ->getMock();
        $this->order->method('setOrderId')->with(1000);
        $this->payment = $this->getMockBuilder('Magento\Sales\Model\Order\Payment')
            ->disableOriginalConstructor()
            ->setMethods(['getOrder', 'setOrder', 'canCapture', 'canRefund'])
            ->getMock();
        $this->payment->method('getOrder')->willReturn($this->order);

        $this->ccMethodMock = $this->getMockBuilder('Magento\Payment\Model\Method\Cc')
            ->disableOriginalConstructor()
            ->setMethods([
                'canVoid',
                'authorize',
                'getConfigData',
                'getConfigPaymentAction',
                'validate',
                'setStore',
                'acceptPayment',
                'denyPayment',
                'fetchTransactionInfo',
                'assignData',
                'validateCcNum',
                'isAvailable',

            ])
            ->getMock();
        $this->reachHelper->method('getSecret')->willReturn("lAjmbItVB0zGiK8o0DLX5yw1W1l6J325Dlro4lKFmKWUefJeteBzc8199mFSpIX3");
        //$this->httpTextFactory->create();
        $this->cc = $objectManager->getObject('Reach\Payment\Model\Cc', [
                'coreSession' => $this->_coresession,
                'storeManager' => $this->storeManager,
                'reachHelper' => $this->reachHelper,
                'reachCurrency' => $this->reachCurrency,
                'reachPayment' => $this->reachPayment,
            ]
        );

    }

    protected function tearDown()
    {
        unset($this->cc);
    }

    /**
     * @param $expectedOutcome
     * @param $isReachEnabled
     * @param $isReachCcEnabled
     * @param $storeId
     * @param $paymentMethod
     * @param $isPaymentMethodAvailable
     * @dataProvider ccDataProvider
     */
    public function testCcIsAvailable($expectedOutcome, $isReachEnabled, $isReachCcEnabled, $storeId, $paymentMethod, $isPaymentMethodAvailable)
    {


        $this->store->method('getId')->willReturn($storeId);
        $this->storeManager->method('getStore')->willReturn($this->store );


        $this->reachHelper->expects($this->once())->method('getReachEnabled')->willReturn($isReachEnabled);
        $this->reachHelper->expects($this->any())->method('getCreditCardActive')->with(self::PATH, $this->storeManager->getStore()->getId())->willReturn($isReachCcEnabled);
        $this->reachPayment->expects($this->any())->method('isAvailable')->with($paymentMethod)->willReturn($isPaymentMethodAvailable);
        $this->assertEquals($expectedOutcome, $this->cc->isAvailable());
    }


    public function ccDataProvider()
    {
        return [
              'Cc Enabled Case 1' => [ true, true, true, 1, 'reach_cc', true],
              'Cc Disabled Case 1' => [ false, false, true, 1, 'reach_cc', true],
              'Cc Disabled Case 2' => [ false, false, false, 1, 'reach_cc', true],
              'Cc Disabled Case 3' => [ false, true, false, 1, 'reach_cc', true],
              'Cc Disabled Case 4' => [ false, true, true, 1, 'reach_cc', false]
        ];
    }

    /*protected function mockCallUrl(){
        return 'placeholder_url'
    }
    protected function mockBuildCheckoutRequest($payment, $amount)
    {
        $request =  [
            'MerchantId' => '12345',
            'ReferenceId' => 1,
            'Consumer' => null,
            'Notify' => 'some url',
            'ConsumerCurrency' =>'CAD',
            'RateOfferId' => 3,
            'DeviceFingerprint' => 'xyzwert',
            'ContractId' =>'contract 1',
            'StashId' =>'xyz',

            'PaymentMethod' => 'reach_cc',
            'OpenContract' => true,
            'Items' =>[
                [
                    'Sku' => 'S12345',
                    'ConsumerPrice'=>34.0,
                    'Quantity' => 2
                ]
            ],
            'ShippingRequired'=> true,
            'Shipping' => [
                'ConsumerDuty' => 7.02,
                'ConsumerPrice' => 34.0,
                'ConsumerTaxes' =>3.0,

            ] ,
            'ConsumerTotal' => 44.02,
            'Capture' => false
        ];
        return $request;
    }

  */

    protected function mockCallCurl()
    {
        return "https:\/\/checkout-sandbox.gointerpay.net\/v2.19\/checkout";

    }
    public function atestAuthorize()
    {
            $request = mockBuildCheckoutRequest(null, null);

            $response = ['response'=> [], 'signature' => ''];

            //$this->cc->method("validateResponse")->with($response['response'], $response['signature'])->willReturn(true);

            $checkOutUrl = 'place_holder_url';
            //$this->cc->method("_buildCheckoutRequest")->with($payment, $amount)->willReturn($request);
            $this->reachHelper->method('getCheckOutUrl')->willReturn($checkOutUrl);
            $this->cc->method("callCurl")->with($checkOutUrl, $request)->willReturn($response);
            var_dump($request);
            var_dump($response);
            $this->assertEquals(true, $response==[]);


            /*$this->buildCheckoutRequestMock = $this->getMockBuilder('Magento\Payment\Model\Method\AbstractMethod')
                ->disableOriginalConstructor()
                ->with($payment, $amount)->willReturn($request);
            $this->callUrl = $this->getMockBuilder('Magento\Payment\Model\Method\AbstractMethod')
            ->disableOriginalConstructor()
            ->with($payment, $amount)->willReturn($request);*/

            $this->assertEqual($this->cc->auth);
    }


    function atestcallUrl($url="https://magento2.test/reach/cc/callback/_store/1/?orderid=7" ,$params=[], $method="POST")
    {    //not right kind to write unit test
        //rest portion

         //$tmp = $this->cc->callCurl($url ,$params, $method);
         //var_dump($tmp);
         $this->assertEquals(false, $tmp["UnderReview"]);
    }



    public function testAuthorize()
    {


        $checkOutUrl = 'place_holder_url';

        $this->reachHelper->method('getCheckOutUrl')->willReturn($checkOutUrl);

        var_dump($this->cc->authorize($this->payment, 56.75));



        //$this->assertEqual();
    }

}