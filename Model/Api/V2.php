<?php
/*
 * @category    Tandym
 * @package     Tandym_Tandympay
 * @copyright   Copyright (c) Tandym (https://www.bytandym.com/)
 */

namespace Tandym\Tandympay\Model\Api;

use DateInterval;
use Exception;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManagerInterface;
use Tandym\Tandympay\Api\Data\AmountInterface;
use Tandym\Tandympay\Api\Data\AmountInterfaceFactory;
use Tandym\Tandympay\Api\Data\AuthorizationInterface;
use Tandym\Tandympay\Api\Data\AuthorizationInterfaceFactory;
use Tandym\Tandympay\Api\Data\CustomerInterface;
use Tandym\Tandympay\Api\Data\CustomerInterfaceFactory;
use Tandym\Tandympay\Api\Data\LinkInterface;
use Tandym\Tandympay\Api\Data\LinkInterfaceFactory;
use Tandym\Tandympay\Api\Data\OrderInterface;
use Tandym\Tandympay\Api\Data\OrderInterfaceFactory;
use Tandym\Tandympay\Api\Data\SessionInterfaceFactory;
use Tandym\Tandympay\Api\Data\SessionOrderInterface;
use Tandym\Tandympay\Api\Data\SessionOrderInterfaceFactory;
use Tandym\Tandympay\Api\Data\SessionTokenizeInterface;
use Tandym\Tandympay\Api\Data\SessionTokenizeInterfaceFactory;
use Tandym\Tandympay\Api\V2Interface;
use Tandym\Tandympay\Helper\Data as TandymHelper;
use Tandym\Tandympay\Model\Tandym;
use Tandym\Tandympay\Model\System\Config\Container\TandymConfigInterface;

/**
 * Class V2
 * @package Tandym\Tandympay\Model\Api
 */
class V2 implements V2Interface
{
    const TANDYM_HOSTED_CHECKOUT_URL = "https://magento.api.platform.poweredbytandym.com/checkout";
    const TANDYM_REFUND_URL = "https://magento.api.platform.poweredbytandym.com/refund";
    
    const TANDYM_VALIDATE_URL_STAGING = "https://api.staging.poweredbytandym.com/paymentsMetadata/validate";
    const TANDYM_VALIDATE_URL_PROD = "https://api.bytandym.com/paymentsMetadata/validate";

    const TANDYM_CAPTURE_VALIDATE_URL = "https://magento.api.platform.poweredbytandym.com/order/capture-and-validate";    
    const TANDYM_EXPRESS_CAPTURE_VALIDATE_URL_STAGING = "https://stagingapi.platform.poweredbytandym.com/express/capture-and-validate";
    const TANDYM_EXPRESS_CAPTURE_VALIDATE_URL_PROD = "https://plugin.api.platform.poweredbytandym.com/express/capture-and-validate";
    
    const TANDYM_AUTH_VALIDATE_URL = "https://magento.api.platform.poweredbytandym.com/order/validate";    
    const TANDYM_EXPRESS_AUTH_VALIDATE_URL_STAGING = "https://stagingapi.platform.poweredbytandym.com/express/validate";
    const TANDYM_EXPRESS_AUTH_VALIDATE_URL_PROD = "https://plugin.api.platform.poweredbytandym.com/express/validate";
    
    const TANDYM_VOID_URL = "https://magento.api.platform.poweredbytandym.com/order/void";    
    const TANDYM_EXPRESS_VOID_URL_STAGING = "https://stagingapi.platform.poweredbytandym.com/express/void";
    const TANDYM_EXPRESS_VOID_URL_PROD = "https://plugin.api.platform.poweredbytandym.com/express/void";
    
    const TANDYM_LIVE_URL = "https://api.bytandym.com";
    const TANDYM_STAGING_URL = "https://api.staging.poweredbytandym.com";

    const TANDYM_EXPRESS_REFUND_URL_PROD = "https://plugin.api.platform.poweredbytandym.com/express/refund";
    const TANDYM_EXPRESS_REFUND_URL_STAGING = "https://stagingapi.platform.poweredbytandym.com/express/refund";

    /**
     * @var TandymConfigInterface
     */
    private $tandymConfig;
    /**
     * @var ProcessorInterface
     */
    private $apiProcessor;
    /**
     * @var JsonHelper
     */
    private $jsonHelper;
    /**
     * @var TandymHelper
     */
    private $tandymHelper;
    /**
     * @var AuthInterfaceFactory
     */
    private $authFactory;
    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var OrderInterfaceFactory
     */
    private $orderInterfaceFactory;
    /**
     * @var AuthorizationInterfaceFactory
     */
    private $authorizationInterfaceFactory;
    /**
     * @var SessionTokenizeInterfaceFactory
     */
    private $sessionTokenizeInterfaceFactory;
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;
    /**
     * @var PayloadBuilder
     */
    private $apiPayloadBuilder;
    /**
     * @var SessionInterfaceFactory
     */
    private $sessionInterfaceFactory;
    /**
     * @var SessionOrderInterfaceFactory
     */
    private $sessionOrderInterfaceFactory;
    /**
     * @var AmountInterfaceFactory
     */
    private $amountInterfaceFactory;
    /**
     * @var TokenizeCustomerInterfaceFactory
     */
    private $tokenizeCustomerInterfaceFactory;
    /**
     * @var LinkInterfaceFactory
     */
    private $linkInterfaceFactory;
    /**
     * @var CustomerInterfaceFactory
     */
    private $customerInterfaceFactory;
    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * V2 constructor.
     * @param AuthInterfaceFactory $authFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param ProcessorInterface $apiProcessor
     * @param TandymHelper $tandymHelper
     * @param JsonHelper $jsonHelper
     * @param StoreManagerInterface $storeManager
     * @param OrderInterfaceFactory $orderInterfaceFactory
     * @param AuthorizationInterfaceFactory $authorizationInterfaceFactory
     * @param SessionTokenizeInterfaceFactory $sessionTokenizeInterfaceFactory
     * @param CheckoutSession $checkoutSession
     * @param PayloadBuilder $apiPayloadBuilder
     * @param SessionInterfaceFactory $sessionInterfaceFactory
     * @param TandymConfigInterface $tandymConfig
     * @param SessionOrderInterfaceFactory $sessionOrderInterfaceFactory
     * @param AmountInterfaceFactory $amountInterfaceFactory
     * @param TokenizeCustomerInterfaceFactory $tokenizeCustomerInterfaceFactory
     * @param LinkInterfaceFactory $linkInterfaceFactory
     * @param CustomerInterfaceFactory $customerInterfaceFactory
     * @param TimezoneInterface $timezone
     */
    public function __construct(
        DataObjectHelper $dataObjectHelper,
        ProcessorInterface $apiProcessor,
        TandymHelper $tandymHelper,
        JsonHelper $jsonHelper,
        StoreManagerInterface $storeManager,
        OrderInterfaceFactory $orderInterfaceFactory,
        AuthorizationInterfaceFactory $authorizationInterfaceFactory,
        SessionTokenizeInterfaceFactory $sessionTokenizeInterfaceFactory,
        CheckoutSession $checkoutSession,
        PayloadBuilder $apiPayloadBuilder,
        SessionInterfaceFactory $sessionInterfaceFactory,
        TandymConfigInterface $tandymConfig,
        SessionOrderInterfaceFactory $sessionOrderInterfaceFactory,
        AmountInterfaceFactory $amountInterfaceFactory,
        LinkInterfaceFactory $linkInterfaceFactory,
        CustomerInterfaceFactory $customerInterfaceFactory,
        TimezoneInterface $timezone
    ) {

        $this->dataObjectHelper = $dataObjectHelper;
        $this->apiProcessor = $apiProcessor;
        $this->tandymConfig = $tandymConfig;
        $this->tandymHelper = $tandymHelper;
        $this->jsonHelper = $jsonHelper;
        $this->storeManager = $storeManager;
        $this->orderInterfaceFactory = $orderInterfaceFactory;
        $this->authorizationInterfaceFactory = $authorizationInterfaceFactory;
        $this->sessionTokenizeInterfaceFactory = $sessionTokenizeInterfaceFactory;
        $this->checkoutSession = $checkoutSession;
        $this->apiPayloadBuilder = $apiPayloadBuilder;
        $this->sessionInterfaceFactory = $sessionInterfaceFactory;
        $this->tandymConfig = $tandymConfig;
        $this->sessionOrderInterfaceFactory = $sessionOrderInterfaceFactory;
        $this->amountInterfaceFactory = $amountInterfaceFactory;
        //$this->tokenizeCustomerInterfaceFactory = $tokenizeCustomerInterfaceFactory;
        $this->linkInterfaceFactory = $linkInterfaceFactory;
        $this->customerInterfaceFactory = $customerInterfaceFactory;
        $this->timezone = $timezone;
    }

    
    /**
     * @inheritDoc
     */
    public function createSession($reference, $storeId)
    {

        $url = self::TANDYM_HOSTED_CHECKOUT_URL;
        $apiKey = $this->tandymConfig->getPublicKey();
        $quote = $this->checkoutSession->getQuote();
        $body = $this->apiPayloadBuilder->buildTandymCheckoutPayload($quote, $reference);
        $originationURL = '';

        $sessionModel = $this->sessionInterfaceFactory->create();
        
        try {
            $response = $this->apiProcessor->call(
                $url,
                $apiKey,
                null,
                $body,
                'POST'
            );
            $apiResult = $this->jsonHelper->jsonDecode($response);
            $originationURL = $apiResult['redirecturl'];
            $body['order'] = array("TandymOrder"=>"TRUE");
            $body['checkout_url'] = $originationURL;
            
            if (isset($body['order']) && ($orderObj = $body['order'])) {
                $body['order']['checkout_url'] = $originationURL;
                $sessionOrderModel = $this->sessionOrderInterfaceFactory->create();
                $this->dataObjectHelper->populateWithArray(
                    $sessionOrderModel,
                    $body['order'],
                    SessionOrderInterface::class
                );

                
                $sessionModel->setOrder($sessionOrderModel);
               
            }
           
            return $sessionModel;
        } catch (Exception $e) {
            $this->tandymHelper->logTandymActions($e->getMessage());
            throw new LocalizedException(
                __('Gateway checkout error: %1', $e->getMessage())
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function release($transId, $orderUUID, $amount, $tandymCheckoutType, $currency, $storeId)
    {
        //$url = $this->tandymConfig->getPaymentMode() == "live" ? self::TANDYM_VALIDATE_URL_PROD : self::TANDYM_VALIDATE_URL_STAGING;
        if ($tandymCheckoutType == "EXPRESS") {
            $url = self::TANDYM_EXPRESS_VOID_STAGING;
            $paymentMode = $this->tandymConfig->getPaymentMode();
            if ($paymentMode == "live") {
                $url = self::TANDYM_EXPRESS_VOID_PROD;
            }
        } else {
            $url =  self::TANDYM_VOID_URL;
        }
        
        $apiKey = $this->tandymConfig->getPublicKey();
        $apiSecret = $this->tandymConfig->getPrivateKey();
        $payload = [
            "paymentID"=> $transId ,
            "orderId"  =>  $orderUUID,
            "amount" => $amount,
            "isTestMode" =>  $this->tandymConfig->getPaymentMode() == "live" ? false : true
        ];

        try {
            $response = $this->apiProcessor->call(
                $url,
                $apiKey,
                $apiSecret,
                $payload,
                'POST'
            );
            $body = $this->jsonHelper->jsonDecode($response);
            return isset($body['void']) && $body['void'] ? $body['void'] : false;
        } catch (Exception $e) {
            $this->tandymHelper->logTandymActions($e->getMessage());
            throw new LocalizedException(
                __('Tandym Gateway Validation Failed error: %1', $e->getMessage())
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function refund($transId, $orderUUID, $amount, $currency, $storeId)
    {
        $url = self::TANDYM_REFUND_URL;
        $apiKey = $this->tandymConfig->getPublicKey();
        $apiSecret = $this->tandymConfig->getPrivateKey();
        $payload = [
            "type"  =>  "refund",
            "orderid"  =>  $orderUUID,
            "transaction_receipt"=> $transId ,
            "amounttotal" => (int)$amount,
            "currency" => $currency,
            "refundType" => "TANDYM_STANDARD_MAGE_REFUND"
        ];
        try {
            $response = $this->apiProcessor->call(
                $url,
                $apiKey,
                $apiSecret,
                $payload,
                'POST'
            );
            $body = $this->jsonHelper->jsonDecode($response);
            return isset($body['referenceid']) && $body['referenceid'] ? $body['referenceid'] : "";
        } catch (Exception $e) {
            $this->tandymHelper->logTandymActions($e->getMessage());
            throw new LocalizedException(
                __('Tandym Gateway refund error: %1', $e->getMessage())
            );
        }
    }

    

    /**
     * @inheritDoc
     */
    public function refundonerror($transId, $orderUUID, $amount, $reason) {
        $url = self::TANDYM_REFUND_URL;
        
        $apiKey = $this->tandymConfig->getPublicKey();
        $apiSecret = $this->tandymConfig->getPrivateKey();
        $this->tandymHelper->logTandymActions("Refund initiated on failed order with key".$apiKey);

        $payload = [
            "type"  =>  "refund",
            "orderid"  =>  $orderUUID,
            "transaction_receipt"=> $transId ,
            "amounttotal" => (int)$amount,
            "currency" => "USD",
            "refundType" => "ORDER FAILURE",
            "refundReason" => $reason
        ];
        try {
            $response = $this->apiProcessor->call(
                $url,
                $apiKey,
                $apiSecret,
                $payload,
                'POST'
            );
            $body = $this->jsonHelper->jsonDecode($response);
            return isset($body['referenceid']) && $body['referenceid'] ? $body['referenceid'] : "TDM-NA";
        } catch (Exception $e) {
            $this->tandymHelper->logTandymActions($e->getMessage());
            throw new LocalizedException(
                __('Tandym Gateway refund error on failed transaction: %1', $e->getMessage())
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function expressrefund($transId, $orderUUID, $amount) {
        $url = self::TANDYM_EXPRESS_REFUND_URL_STAGING;

        $paymentMode = $this->tandymConfig->getPaymentMode();

        if ($paymentMode == "live") {
            $url = self::TANDYM_EXPRESS_REFUND_URL_PROD;
        }
        
        $apiKey = $this->tandymConfig->getPublicKey();
        $apiSecret = $this->tandymConfig->getPrivateKey();
        $this->tandymHelper->logTandymActions("Refund initiated on failed order with key".$apiKey);

        $payload = [
            "type"  =>  "refund",
            "orderid"  =>  $orderUUID,
            "transaction_receipt"=> $transId ,
            "amounttotal" => (int)$amount,
            "currency" => "USD",
            "refundType" => "TANDYM_EXPRESS_MAGE_REFUND"
        ];
        try {
            $response = $this->apiProcessor->call(
                $url,
                $apiKey,
                $apiSecret,
                $payload,
                'POST'
            );
            $body = $this->jsonHelper->jsonDecode($response);
            return isset($body['referenceid']) && $body['referenceid'] ? $body['referenceid'] : "TDM-NA";
        } catch (Exception $e) {
            $this->tandymHelper->logTandymActions($e->getMessage());
            throw new LocalizedException(
                __('Tandym Gateway refund error on failed transaction: %1', $e->getMessage())
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function validatepayment($transId, $orderUUID, $amount, $currency, $storeId, $transType = "AUTHORIZE_CAPTURE")
    {
        //$url = $this->tandymConfig->getPaymentMode() == "live" ? self::TANDYM_VALIDATE_URL_PROD : self::TANDYM_VALIDATE_URL_STAGING;
        if ($transType == "AUTHORIZE_CAPTURE") {
            $url =  self::TANDYM_CAPTURE_VALIDATE_URL;
        } else {
            $url =  self::TANDYM_AUTH_VALIDATE_URL;
        }
        
        $apiKey = $this->tandymConfig->getPublicKey();
        $apiSecret = $this->tandymConfig->getPrivateKey();
        $payload = [
            "paymentID"=> $transId ,
            "orderId"  =>  $orderUUID,
            "amount" => $amount,
            "isTestMode" =>  $this->tandymConfig->getPaymentMode() == "live" ? false : true
        ];

        try {
            $response = $this->apiProcessor->call(
                $url,
                $apiKey,
                $apiSecret,
                $payload,
                'POST'
            );
            $body = $this->jsonHelper->jsonDecode($response);
            return isset($body['valid']) && $body['valid'] ? $body['valid'] : false;
        } catch (Exception $e) {
            $this->tandymHelper->logTandymActions($e->getMessage());
            throw new LocalizedException(
                __('Tandym Gateway Validation Failed error: %1', $e->getMessage())
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function validateexpresspayment($transId, $orderUUID, $amount, $currency, $storeId, $transType = "AUTHORIZE_CAPTURE")
    {
        if ($transType == "AUTHORIZE_CAPTURE") {
            $url = $this->tandymConfig->getPaymentMode() == "live" ? self::TANDYM_EXPRESS_CAPTURE_VALIDATE_URL_PROD : self::TANDYM_EXPRESS_CAPTURE_VALIDATE_URL_STAGING;
        } else {
            $url = $this->tandymConfig->getPaymentMode() == "live" ? self::TANDYM_EXPRESS_AUTH_VALIDATE_URL_PROD : self::TANDYM_EXPRESS_AUTH_VALIDATE_URL_STAGING;
        }
        
        $apiKey = $this->tandymConfig->getPublicKey();
        $apiSecret = $this->tandymConfig->getPrivateKey();
        $payload = [
            "paymentID"=> $transId ,
            "orderId"  =>  $orderUUID,
            "amount" => $amount,
            "isTestMode" =>  $this->tandymConfig->getPaymentMode() == "live" ? false : true
        ];
        try {
            $response = $this->apiProcessor->call(
                $url,
                $apiKey,
                $apiSecret,
                $payload,
                'POST'
            );
            $body = $this->jsonHelper->jsonDecode($response);
            return isset($body['valid']) && $body['valid'] ? $body['valid'] : false;
        } catch (Exception $e) {
            $this->tandymHelper->logTandymActions($e->getMessage());
            throw new LocalizedException(
                __('Tandym Gateway Validation Failed error: %1', $e->getMessage())
            );
        }
    }

}
