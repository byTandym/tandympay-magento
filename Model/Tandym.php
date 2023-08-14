<?php
/*
 * @category    Tandym
 * @package     Tandym_Tandympay
 * @copyright   Copyright (c) Tandym (https://www.bytandym.com/)
 */

namespace Tandym\Tandympay\Model;

use Exception;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Payment\Model\Method\Logger;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteRepository;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Tandym\Tandympay\Api\V2Interface;
use Tandym\Tandympay\Helper\Data;
use Tandym\Tandympay\Helper\Util;
use Tandym\Tandympay\Model\System\Config\Container\TandymIdentity;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResourceModel;

/**
 * Class Tandym
 * @package Tandym\Tandympay\Model
 */
class Tandym extends AbstractMethod
{
    const PAYMENT_CODE = 'tandympay';
    const ADDITIONAL_INFORMATION_KEY_REFERENCE_ID = 'tandym_reference_id';
    const ADDITIONAL_INFORMATION_KEY_ORIGINAL_ORDER_UUID = 'tandym_original_order_uuid';
    const ADDITIONAL_INFORMATION_KEY_EXTENDED_ORDER_UUID = 'tandym_extended_order_uuid';
    const TANDYM_AUTH_EXPIRY = 'tandym_auth_expiry';
    const TANDYM_CAPTURE_EXPIRY = 'tandym_capture_expiry';
    const TANDYM_ORDER_TYPE = 'tandym_order_type';
    const TANDYM_CHECKOUT_TYPE = 'tandym_checkout_type';

    const ADDITIONAL_INFORMATION_KEY_REFERENCE_ID_V1 = 'tandym_order_id';

    const ADDITIONAL_INFORMATION_KEY_AUTH_AMOUNT = 'tandym_auth_amount';
    const ADDITIONAL_INFORMATION_KEY_CAPTURE_AMOUNT = 'tandym_capture_amount';
    const ADDITIONAL_INFORMATION_KEY_REFUND_AMOUNT = 'tandym_refund_amount';
    const ADDITIONAL_INFORMATION_KEY_RELEASE_AMOUNT = 'tandym_order_amount';

    const ADDITIONAL_INFORMATION_KEY_GET_ORDER_LINK = 'tandym_get_order_link';
    const ADDITIONAL_INFORMATION_KEY_CAPTURE_LINK = 'tandym_capture_link';
    const ADDITIONAL_INFORMATION_KEY_REFUND_LINK = 'tandym_refund_link';
    const ADDITIONAL_INFORMATION_KEY_RELEASE_LINK = 'tandym_release_link';
    const ADDITIONAL_INFORMATION_KEY_CREATE_ORDER_LINK = 'tandym_create_order_link';
    const ADDITIONAL_INFORMATION_KEY_GET_CUSTOMER_LINK = 'tandym_get_customer_link';
    const ADDITIONAL_INFORMATION_KEY_GET_TOKEN_DETAILS_LINK = 'tandym_token_link';

    /**
     * @var string
     */
    protected $_code = self::PAYMENT_CODE;
    /**
     * @var bool
     */
    protected $_isGateway = true;
    /**
     * @var bool
     */
    protected $_isInitializeNeeded = true;
    /**
     * @var bool
     */
    protected $_canOrder = true;
    /**
     * @var bool
     */
    protected $_canAuthorize = true;
    /**
     * @var bool
     */
    protected $_canCapture = true;

    /**
     * @var bool
     */
    protected $_canCapturePartial = true;
    /**
     * @var bool
     */
    protected $_canRefund = true;
    /**
     * @var bool
     */
    protected $_canVoid = true;
    /**
     * @var bool
     */
    protected $_canRefundInvoicePartial = true;
    /**
     * @var bool
     */
    protected $_canUseInternal = false;
    /**
     * @var bool
     */
    protected $_canFetchTransactionInfo = true;

    /**
     * @var Data
     */
    protected $tandymHelper;

    /**
     * @var V2Interface
     */
    protected $v2;
    /**
     * @var QuoteRepository
     */
    private $quoteRepository;
    /**
     * @var CustomerSession
     */
    protected $customerSession;
    /**
     * @var System\Config\Container\TandymConfigInterface
     */
    private $tandymConfig;
    /**
     * @var Tokenize
     */
    private $tokenizeModel;
    /**
     * @var V1Interface
     */
    private $v1;
    /**
     * @var DateTime
     */
    private $dateTime;
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;
    /**
     * @var QuoteResourceModel
     */
    private $quoteResourceModel;

    /**
     * Tandym constructor.
     * @param Context $context
     * @param System\Config\Container\TandymConfigInterface $tandymConfig
     * @param Data $tandymHelper
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param ScopeConfigInterface $scopeConfig
     * @param Logger $mageLogger
     * @param QuoteRepository $quoteRepository
     * @param V2Interface $v2
     * @param CustomerSession $customerSession
     * @param Tokenize $tokenizeModel
     * @param V1Interface $v1
     * @param DateTime $dateTime
     * @param CheckoutSession $checkoutSession
     * @param QuoteResourceModel $quoteResourceModel
     */
    public function __construct(
        Context $context,
        System\Config\Container\TandymConfigInterface $tandymConfig,
        Data $tandymHelper,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        ScopeConfigInterface $scopeConfig,
        Logger $mageLogger,
        QuoteRepository $quoteRepository,
        V2Interface $v2,
        CustomerSession $customerSession,
        DateTime $dateTime,
        CheckoutSession $checkoutSession,
        QuoteResourceModel $quoteResourceModel
    ) {

        $this->tandymConfig = $tandymConfig;
        $this->tandymHelper = $tandymHelper;
        
        $this->quoteRepository = $quoteRepository;
        $this->v2 = $v2;
        $this->customerSession = $customerSession;
        $this->dateTime = $dateTime;
        $this->checkoutSession = $checkoutSession;
        $this->quoteResourceModel = $quoteResourceModel;
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $mageLogger
        );
    }

    /**
     * Get Tandym checkout url
     *
     * @param Quote $quote
     * @return string
     * @throws LocalizedException
     * @throws Exception
     */
    public function getTandymRedirectUrl($quote)
    {
        
        $payment = $quote->getPayment();
        $referenceID = $payment->getAdditionalInformation(self::ADDITIONAL_INFORMATION_KEY_REFERENCE_ID);
        $this->tandymHelper->logTandymActions("Reference Id : $referenceID");
        $this->tandymHelper->logTandymActions("Payment Type : " . $this->getConfigPaymentAction());
        $additionalInformation[self::ADDITIONAL_INFORMATION_KEY_REFERENCE_ID] = $referenceID;
        

        $redirectURL = '';
        
        $this->tandymHelper->logTandymActions("Typical Tandym Checkout");

        $session = $this->v2->createSession($referenceID, $quote->getStoreId());

        if ($session->getOrder()) {
            
            $redirectURL = $session->getOrder()->getCheckoutUrl();

            if ($session->getOrder()->getUuid()) {
                $orderUUID = [
                    self::ADDITIONAL_INFORMATION_KEY_ORIGINAL_ORDER_UUID => $session->getOrder()->getUuid()
                ];
                $this->tandymHelper->logTandymActions("Typical ADDITIONAL_INFORMATION_KEY_ORIGINAL_ORDER_UUID".$session->getOrder()->getUuid());
                $additionalInformation = array_merge($additionalInformation, $orderUUID);
            }
          
        }
            
        if (!$redirectURL) {
            $this->tandymHelper->logTandymActions("Redirect URL was not received from Tandym.");
            throw new LocalizedException(__('Unable to start your checkout with Tandym.'));
        }
        $payment->setAdditionalInformation(array_merge(
            $additionalInformation,
            [self::TANDYM_ORDER_TYPE => TandymIdentity::API_VERSION_V2]
        ));
        $this->quoteResourceModel->save($quote->collectTotals());
        $this->checkoutSession->replaceQuote($quote);
        $this->tandymHelper->logTandymActions("Checkout URL : $redirectURL");
        return $redirectURL;
    }

    /**
     * @param string $paymentAction
     * @param object $stateObject
     * @return Tandym|void
     * @throws LocalizedException
     */
    public function initialize($paymentAction, $stateObject)
    {
        $this->tandymHelper->logTandymActions("Payment Initialize : ".$paymentAction);
        switch ($paymentAction) {
            case self::ACTION_AUTHORIZE:
                $payment = $this->getInfoInstance();
                /** @var Order $order */
                $order = $payment->getOrder();
                $order->setCanSendNewEmailFlag(false);
                $payment->authorize(true, $order->getBaseTotalDue()); // base amount will be set inside
                $payment->setAmountAuthorized($order->getTotalDue());
                $orderStatus = $order->getConfig()->getStateDefaultStatus(Order::STATE_NEW);
                $order->setCustomerNote("Payment authorized by Tandym.");
                $stateObject->setState(Order::STATE_NEW);
                $stateObject->setStatus($orderStatus);
                $stateObject->setIsNotified(true);
                break;
            case self::ACTION_AUTHORIZE_CAPTURE:
                $payment = $this->getInfoInstance();
                /** @var Order $order */
                $order = $payment->getOrder();
                $order->setCanSendNewEmailFlag(false);
                $payment->capture(null);
                $orderStatus = $order->getConfig()->getStateDefaultStatus(Order::STATE_PROCESSING);
                $order->setCustomerNote("Payment captured by Tandym.");
                $stateObject->setState(Order::STATE_PROCESSING);
                $stateObject->setStatus($orderStatus);
                $stateObject->setIsNotified(true);
                break;
            default:
                break;
        }
    }

    /**
     * Send authorize request to gateway
     *
     * @param DataObject|InfoInterface $payment
     * @param float $amount
     * @return Tandym
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws LocalizedException
     */
    public function authorize(InfoInterface $payment, $amount)
    {
        if (!$this->canAuthorize()) {
            throw new LocalizedException(__('The authorize action is not available.'));
        } elseif ($amount <= 0) {
            throw new LocalizedException(__('Invalid amount for authorize.'));
        }
        $this->tandymHelper->logTandymActions("****Authorization start****");
        $reference = $payment->getAdditionalInformation(self::ADDITIONAL_INFORMATION_KEY_REFERENCE_ID);
        $tandymOrderUUID = $payment->getAdditionalInformation(self::ADDITIONAL_INFORMATION_KEY_ORIGINAL_ORDER_UUID);

        $this->tandymHelper->logTandymActions("Incoming amount from Magento : $amount");
        $amountInCents = Util::formatToCents($amount);
        $this->tandymHelper->logTandymActions("Amount In Cents : $amountInCents");
        $this->trackCartItemInformation($payment);

        $this->tandymHelper->logTandymActions("Tandym Reference ID : $reference");
       
        if (!$this->validateOrder($payment)) {
            throw new LocalizedException(__('Unable to validate the order.'));
        }
        $this->tandymHelper->logTandymActions("Order validated at Tandym");
        $this->tandymHelper->logTandymActions("Order UUID : $tandymOrderUUID");
        $authorizedAmount = $payment->getAdditionalInformation(self::ADDITIONAL_INFORMATION_KEY_AUTH_AMOUNT);
        $authorizedAmount += $amount;
        $payment->setAdditionalInformation(self::ADDITIONAL_INFORMATION_KEY_AUTH_AMOUNT, $authorizedAmount);
        $payment->setAdditionalInformation('payment_type', $this->getConfigPaymentAction());
        $payment->setTransactionId($tandymOrderUUID)->setIsTransactionClosed(false);
        $this->tandymHelper->logTandymActions("Transaction ID : $tandymOrderUUID");
        $this->tandymHelper->logTandymActions("Authorization successful");
        $this->tandymHelper->logTandymActions("Authorization end");
        return $this;
    }

    /**
     * Capture at Magento
     *
     * @param InfoInterface $payment
     * @param float $amount
     * @return Tandym
     * @throws LocalizedException
     */
    public function capture(InfoInterface $payment, $amount)
    {
        $this->tandymHelper->logTandymActions("****Capture at Magento start****");
        if (!$this->canCapture()) {
            throw new LocalizedException(__('The capture action is not available.'));
        } elseif ($amount <= 0) {
            throw new LocalizedException(__('Invalid amount for capture.'));
        }
        $this->tandymHelper->logTandymActions("Incoming amount from Magento : $amount");
        $amountInCents = Util::formatToCents($amount);
        $this->tandymHelper->logTandymActions("Amount In Cents : $amountInCents");
        $this->trackCartItemInformation($payment);

        $payment->setAdditionalInformation('payment_type', $this->getConfigPaymentAction());
        $tandymOrderUUID = $payment->getAdditionalInformation(self::ADDITIONAL_INFORMATION_KEY_ORIGINAL_ORDER_UUID);
        
        // if (!$this->validateOrder($payment, $this->canInvoice($payment->getOrder()))) {
        //     throw new LocalizedException(__('Unable to validate the order.'));
        // }
        if (!$this->validateTandymOrder($payment, $amount)){
            throw new LocalizedException(__('Unable to validate the order with Tandym.'));
        }

        $this->tandymHelper->logTandymActions("Order validated at Tandym");
        $this->tandymHelper->logTandymActions("Order UUID : $tandymOrderUUID");
        $tandymOrderType = $payment->getAdditionalInformation(self::TANDYM_ORDER_TYPE);
        $this->tandymHelper->logTandymActions("Tandym Order Type : $tandymOrderType");
        switch ($tandymOrderType) {
            case TandymIdentity::API_VERSION_V2:
                $captureTxnID = $this->handleV2Capture($tandymOrderUUID, $payment, $amount);
                break;
            default:
                $captureTxnID = $this->handleV2Capture($tandymOrderUUID, $payment, $amount);
                break;
        }
        if (!$captureTxnID) {
            $this->tandymHelper->logTandymActions("Capture failed at Tandym.");
            throw new LocalizedException(__('Unable to capture the amount.'));
        }
        $payment->setTransactionId($captureTxnID)->setIsTransactionClosed(false);
        $this->tandymHelper->logTandymActions("Transaction ID : $captureTxnID");
        $this->tandymHelper->logTandymActions("****Capture at Magento end****");
        return $this;
    }

    /**
     * @param InfoInterface $payment
     * @return $this|Tandym
     * @throws LocalizedException
     */
    public function _void(InfoInterface $payment)
    {
        $this->tandymHelper->logTandymActions("****Release Started****");
        if (!$this->canVoid()) {
            throw new LocalizedException(__('The void action is not available.'));
        } elseif (!$this->validateOrder($payment)) {
            throw new LocalizedException(__('Unable to validate the order.'));
        } elseif (!$orderUUID = $payment->getAdditionalInformation(self::ADDITIONAL_INFORMATION_KEY_ORIGINAL_ORDER_UUID)) {
            throw new LocalizedException(__('Failed to void the payment.'));
        }
        $this->tandymHelper->logTandymActions("Order validated at Tandym");
        $amountInCents = Util::formatToCents($payment->getOrder()->getBaseGrandTotal());

        $url = $payment->getAdditionalInformation(self::ADDITIONAL_INFORMATION_KEY_RELEASE_LINK);
        if (!$isReleased = $this->v2->release(
            $url,
            $orderUUID,
            $amountInCents,
            $payment->getOrder()->getBaseCurrencyCode(),
            $payment->getOrder()->getStoreId()
        )) {
            $this->tandymHelper->logTandymActions("Release failed at Tandym.");
            throw new LocalizedException(__('Failed to void the payment.'));
        }
        $payment->setAdditionalInformation(
            self::ADDITIONAL_INFORMATION_KEY_RELEASE_AMOUNT,
            $payment->getOrder()->getBaseGrandTotal()
        );
        $payment->getOrder()->setState(Order::STATE_CLOSED)
            ->setStatus($payment->getOrder()->getConfig()->getStateDefaultStatus(Order::STATE_CLOSED));
        $this->tandymHelper->logTandymActions("Released payment successfully");
        $this->tandymHelper->logTandymActions("****Release end****");

        return $this;
    }

    /**
     * Refund payment
     *
     * @param InfoInterface $payment
     * @param float $amount
     * @return $this|Tandym
     * @throws LocalizedException
     */
    public function refund(InfoInterface $payment, $amount)
    {
        $this->tandymHelper->logTandymActions("****Refund Started****");
        if (!$this->canRefund()) {
            throw new LocalizedException(__('The refund action is not available.'));
        } elseif ($amount <= 0) {
            throw new LocalizedException(__('Invalid amount for refund.'));
        } elseif (!$this->validateOrder($payment)) {
            throw new LocalizedException(__('Unable to validate the order.'));
        }
        $this->tandymHelper->logTandymActions("Order validated at Tandym");
        $amountInCents = Util::formatToCents($amount);
        $tandymOrderType = $payment->getAdditionalInformation(self::TANDYM_ORDER_TYPE);
        $tandymCheckoutType = $payment->getAdditionalInformation(self::TANDYM_CHECKOUT_TYPE);
        $tmpRefId = $payment->getCreditMemo()->getInvoice()->getTransactionId();
        
        //$this->tandymHelper->logTandymActions("Order Refund Started for getCreditMemo()->getInvoice()->getTransactionId() at Tandym - txtRef - ".$tmpRefId);
        //$this->tandymHelper->logTandymActions("Order Refund Started for getCreditMemo()->getInvoice()->getTransactionId() at Tandym - txnUUID - ".$payment->getAdditionalInformation($tmpRefId));
        
        if ($tandymOrderType == TandymIdentity::API_VERSION_V2 && $tandymCheckoutType != 'EXPRESS') {
            if (!$txnUUID = $payment->getCreditMemo()->getInvoice()->getTransactionId()) {
                throw new LocalizedException(__('Failed to refund the payment. Parent Transaction ID is missing.'));
            } elseif (!$tandymOrderUUID = $payment->getAdditionalInformation($txnUUID)) {
                throw new LocalizedException(__('Failed to refund the payment. Order UUID is missing.'));
            }
            $payment->setAdditionalInformation(self::ADDITIONAL_INFORMATION_KEY_REFUND_AMOUNT, $amountInCents);
            $refundTxnUUID = $this->v2->refund(
                $txnUUID,
                $tandymOrderUUID,
                $amountInCents,
                $payment->getOrder()->getBaseCurrencyCode(),
                $payment->getOrder()->getStoreId()
            );
        } else {
           // refund for express checkout

            if (!$txnUUID = $payment->getCreditMemo()->getInvoice()->getTransactionId()) {
                throw new LocalizedException(__('Failed to refund the payment. Parent Transaction ID is missing.'));
            } elseif (!$tandymOrderUUID = $payment->getAdditionalInformation($txnUUID)) {
                throw new LocalizedException(__('Failed to refund the payment. Order UUID is missing.'));
            }
            $payment->setAdditionalInformation(self::ADDITIONAL_INFORMATION_KEY_REFUND_AMOUNT, $amountInCents);
            $refundTxnUUID = $this->v2->expressrefund(
                $txnUUID,
                $tandymOrderUUID,
                $amountInCents,
                $payment->getOrder()->getBaseCurrencyCode(),
                $payment->getOrder()->getStoreId()
            );
        }
        if (!$refundTxnUUID) {
            $this->tandymHelper->logTandymActions("Refund failed at Tandym.");
            throw new LocalizedException(__('Failed to refund the payment.'));
        }
        if ($tandymOrderType == TandymIdentity::API_VERSION_V2) {
            $refundedAmount = $payment->getAdditionalInformation(self::ADDITIONAL_INFORMATION_KEY_REFUND_AMOUNT);
            $this->tandymHelper->logTandymActions("Order Refund Started - Initial Amount : ".$refundedAmount);
            $refundedAmount += $amount;
            $payment->setAdditionalInformation(self::ADDITIONAL_INFORMATION_KEY_REFUND_AMOUNT, $refundedAmount);
        }
        $payment->setTransactionId($refundTxnUUID)->setIsTransactionClosed(true);
        $this->tandymHelper->logTandymActions("Refunded payment successfully");
        $this->tandymHelper->logTandymActions("****Refund end****");

        return $this;
    }

    /**
     * Track Cart Item Information
     *
     * @param InfoInterface $payment
     */
    private function trackCartItemInformation(InfoInterface $payment)
    {
        try {
            if ($quoteId = $payment->getOrder()->getQuoteId()) {
                $quote = $this->quoteRepository->get($quoteId);
                $this->tandymHelper->logTandymActions("Collecting Quote Item Information");
                foreach ($quote->getAllVisibleItems() as $item) {
                    $this->tandymHelper->logTandymActions(
                        "Sku : " . $item->getSku() .
                        " | " . "Qty : " . $item->getQty() .
                        " | " . "Price : " . $item->getPrice()
                    );
                }
                $this->tandymHelper->logTandymActions("Collection done");
            }
        } catch (Exception $e) {
            $this->tandymHelper->logTandymActions($e->getMessage());
        }
    }

    /**
     * Check partial capture availability
     *
     * @return bool
     * 
     */
    public function canCapturePartial()
    {
        return $this->tandymConfig->getGatewayRegion() === 'IN' ? false : $this->_canCapturePartial;
    }

    /**
     * Check void availability.
     *
     * @return bool
     * 
     */
    public function canVoid()
    {
        return $this->tandymConfig->getGatewayRegion() === 'IN' ? false : $this->_canVoid;
    }

    /**
     * Check whether payment method can be used
     *
     * @param CartInterface|null $quote
     * @return bool
     * @throws LocalizedException
     */
    public function isAvailable(CartInterface $quote = null)
    {
        if (!$this->isActive($quote ? $quote->getStoreId() : null)) {
            return false;
        }

        $checkResult = new DataObject();
        $checkResult->setData('is_available', true);

        $publicKey = $this->tandymConfig->getPublicKey();
        $privateKey = $this->tandymConfig->getPrivateKey();


        if (($this->getCode() == self::PAYMENT_CODE)
            && ((!$publicKey || !$privateKey)
                )) {
            $checkResult->setData('is_available', false);
        }

        return $checkResult->getData('is_available');
    }

    /**
     * Validate Order
     *
     * @param InfoInterface $payment
     * @param bool $isAuthValid
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function validateOrder($payment, $isAuthValid = true)
    {
        $this->tandymHelper->logTandymActions("Inside Validate Order Method - Call to Action");
        return true;
    }

    /**
     * Validate Order
     *
     * @param InfoInterface $payment
     * @param float $amount
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function validateTandymOrder($payment, $amount)
    {
        $tandymOrderUUID = $payment->getAdditionalInformation(self::ADDITIONAL_INFORMATION_KEY_ORIGINAL_ORDER_UUID);
        $captureTxnUUID = $payment->getAdditionalInformation(self::ADDITIONAL_INFORMATION_KEY_REFERENCE_ID);
        
        $tandymCheckoutType = $payment->getAdditionalInformation(self::TANDYM_CHECKOUT_TYPE);

        $this->tandymHelper->logTandymActions("Inside Validate Order Method - Call to Action");

        // if (!is_string($captureTxnUUID) || (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $captureTxnUUID) !== 1)) {
        //     $this->tandymHelper->logTandymActions("Inside Tandym Receipt - ".$captureTxnUUID);
        //     return false;
        // }
        $orderTandymValidated = false;
        if ($tandymCheckoutType != 'EXPRESS') {
            $orderTandymValidated = $this->v2->validatepayment(
                $captureTxnUUID,
                $tandymOrderUUID,
                $amount,
                $payment->getOrder()->getBaseCurrencyCode(),
                $payment->getOrder()->getStoreId()
            );
        } else {
            $orderTandymValidated = $this->v2->validateexpresspayment(
                $captureTxnUUID,
                $tandymOrderUUID,
                $amount,
                $payment->getOrder()->getBaseCurrencyCode(),
                $payment->getOrder()->getStoreId()
            );
        }

        $this->tandymHelper->logTandymActions("Inside Validate Order Method - Checkout Type - ". $tandymCheckoutType." - Response - ".$orderTandymValidated);
        return $orderTandymValidated;
    }

    /**
     * Set Tandym Auth Expiry
     *
     * @param OrderInterface $order
     * @return void
     * @throws LocalizedException
     */
    public function _setTandymAuthExpiry($order)
    {
        $tandymOrderUUID = $order->getPayment()->getAdditionalInformation(self::ADDITIONAL_INFORMATION_KEY_ORIGINAL_ORDER_UUID);
        $url = $order->getPayment()->getAdditionalInformation(self::ADDITIONAL_INFORMATION_KEY_GET_ORDER_LINK);
        $tandymOrder = $this->v2->getOrder((string)$url, (string)$tandymOrderUUID, $order->getStoreId());
        if ($auth = $tandymOrder->getAuthorization()) {
            $order->getPayment()->setAdditionalInformation(self::TANDYM_AUTH_EXPIRY, $auth->getExpiration())->save();
        }
    }

    /**
     * Check if invoice can be created or not
     *
     * @param OrderInterface $order
     * @return bool
     */
    public function canInvoice($order)
    {
        $paymentType = $order->getPayment()->getAdditionalInformation('payment_type');
        // if ($order->getPayment()->getMethod() == Tandym::PAYMENT_CODE
        //     && $paymentType === self::ACTION_AUTHORIZE) {
        //     $tandymOrderType = $order->getPayment()->getAdditionalInformation(self::TANDYM_ORDER_TYPE);
        //     $currentTimestamp = $this->dateTime->timestamp('now');
        //     if ($tandymOrderType == TandymIdentity::API_VERSION_V2) {
        //         $authExpiry = $order->getPayment()->getAdditionalInformation(self::TANDYM_AUTH_EXPIRY);
        //         $expirationTimestamp = $this->dateTime->timestamp($authExpiry);
        //     } else {
        //         $captureExpiry = $order->getPayment()->getAdditionalInformation(self::TANDYM_CAPTURE_EXPIRY);
        //         $expirationTimestamp = $this->dateTime->timestamp($captureExpiry);
        //     }
        //     if ($expirationTimestamp < $currentTimestamp) {
        //         $this->tandymHelper->logTandymActions("Authorization expired.");
        //         return false;
        //     }
        // }
        $this->tandymHelper->logTandymActions("Authorization valid.");
        return true;
    }

    
    /**
     * Handling of V2 Capture
     *
     * @param string $tandymOrderUUID
     * @param InfoInterface $payment
     * @param int $amount
     * @return string
     */
    private function handleV2Capture($tandymOrderUUID, $payment, $amount)
    {
        $this->tandymHelper->logTandymActions($tandymOrderUUID);
        $amountInCents = Util::formatToCents($amount);
        $isPartialCapture = $payment->formatAmount($payment->getOrder()->getBaseGrandTotal(), true)
            != $payment->formatAmount($amount, true);
            
        $captureTxnUUID = $payment->getAdditionalInformation(self::ADDITIONAL_INFORMATION_KEY_REFERENCE_ID);
        // $captureTxnUUID = $this->v2->capture(
        //     "",
        //     $tandymOrderUUID,
        //     $amountInCents,
        //     $isPartialCapture,
        //     $payment->getOrder()->getBaseCurrencyCode(),
        //     $payment->getOrder()->getStoreId()
        // );
        if (!$payment->getAdditionalInformation(self::ADDITIONAL_INFORMATION_KEY_ORIGINAL_ORDER_UUID)) {
            $payment->setAdditionalInformation(
                self::ADDITIONAL_INFORMATION_KEY_ORIGINAL_ORDER_UUID,
                $tandymOrderUUID
            );
        }
        $capturedAmount = $payment->getAdditionalInformation(self::ADDITIONAL_INFORMATION_KEY_CAPTURE_AMOUNT);
        $capturedAmount += $amount;
        if (!$authAmount = $payment->getAdditionalInformation(self::ADDITIONAL_INFORMATION_KEY_AUTH_AMOUNT)) {
            $payment->setAdditionalInformation(self::ADDITIONAL_INFORMATION_KEY_AUTH_AMOUNT, $capturedAmount);
        }
        $payment->setAdditionalInformation(self::ADDITIONAL_INFORMATION_KEY_CAPTURE_AMOUNT, $capturedAmount);
        $payment->setAdditionalInformation($captureTxnUUID, $tandymOrderUUID);
        return $captureTxnUUID;
    }
    
}
