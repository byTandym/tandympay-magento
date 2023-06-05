<?php
/*
 * @category    Tandym
 * @package     Tandym_Tandympay
 * @copyright   Copyright (c) Tandym (https://www.tandym.com/)
 */

namespace Tandym\Tandympay\Model\Order;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\UrlInterface;
use Magento\Quote\Api\CartManagementInterface;
use Tandym\Tandympay\Model\CheckoutValidator;
use Tandym\Tandympay\Model\Tandym;

/**
 * Class SaveHandler
 * @package Tandym\Tandympay\Model\Order
 */
class SaveHandler
{

    /**
     * @var CustomerSession
     */
    protected $customerSession;
    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;
    /**
     * @var Tandym
     */
    protected $tandymModel;
    /**
     * @var Data
     */
    protected $jsonHelper;

    /**
     * @var \Tandym\Tandympay\Helper\Data
     */
    protected $tandymHelper;
    /**
     * @var UrlInterface
     */
    private $url;
    /**
     * @var CheckoutValidator
     */
    private $checkoutValidator;
    /**
     * @var CartManagementInterface
     */
    private $cartManagement;
    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * SaveHandler constructor.
     * @param CustomerSession $customerSession
     * @param CheckoutSession $checkoutSession
     * @param Tandym $tandymModel
     * @param \Tandym\Tandympay\Helper\Data $tandymHelper
     * @param Data $jsonHelper
     * @param UrlInterface $url
     * @param CheckoutValidator $checkoutValidator
     * @param CartManagementInterface $cartManagement
     * @param ProductMetadataInterface $productMetadata
     */
    public function __construct(
        CustomerSession $customerSession,
        CheckoutSession $checkoutSession,
        Tandym $tandymModel,
        \Tandym\Tandympay\Helper\Data $tandymHelper,
        Data $jsonHelper,
        UrlInterface $url,
        CheckoutValidator $checkoutValidator,
        CartManagementInterface $cartManagement,
        ProductMetadataInterface $productMetadata,
        \Magento\Quote\Model\QuoteFactory $quoteFactory
    ) {
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->tandymModel = $tandymModel;
        $this->tandymHelper = $tandymHelper;
        $this->jsonHelper = $jsonHelper;
        $this->url = $url;
        $this->checkoutValidator = $checkoutValidator;
        $this->cartManagement = $cartManagement;
        $this->productMetadata = $productMetadata;
        $this->quoteFactory = $quoteFactory;
    }

    /**
     * Start Tandym Checkout
     *
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function createCheckout()
    {
        $quote = $this->checkoutSession->getQuote();
        // $quote->setCustomerId(null);
        // $quote->setCustomerEmail($quote->getBillingAddress()->getEmail());
        // $quote->setCustomerIsGuest(true);
        // $quote->setCustomerGroupId(\Magento\Customer\Api\Data\GroupInterface::NOT_LOGGED_IN_ID);

        $magentoVersion = $this->productMetadata->getEdition() . " " . $this->productMetadata->getVersion();
        $tandymVersion = $this->tandymHelper->getVersion();
        $this->tandymHelper->logTandymActions(sprintf("Magento Version : %s | Tandym Version : %s", $magentoVersion, $tandymVersion));
        $this->tandymHelper->logTandymActions("****Starting Tandym Checkout****");
        $this->tandymHelper->logTandymActions("Quote Id : " . $quote->getId());
        $this->tandymHelper->logTandymActions("Customer Id : " . $quote->getCustomer()->getId());
        // $this->tandymHelper->logTandymActions("Customer Email : " . $quote->getCustomerEmail());
        
        $this->checkoutValidator->validate($quote);

        $payment = $quote->getPayment();
        $quote->reserveOrderId();
        $this->tandymHelper->logTandymActions("Order ID from quote : " . $quote->getReservedOrderId());
        $referenceID = uniqid() . "-" . $quote->getReservedOrderId();
        $additionalInformation[Tandym::ADDITIONAL_INFORMATION_KEY_REFERENCE_ID] = $referenceID;
        $payment->setAdditionalInformation($additionalInformation);
        $quote->setPayment($payment);

        $this->tandymHelper->logTandymActions("Process Payload for Tandym Checkout API and Handle the REDIRECT URL");

        $checkoutUrl = $this->tandymModel->getTandymRedirectUrl($quote);
                
        return $this->jsonHelper->jsonEncode(["checkout_url" => $checkoutUrl]);
    }


    public function completeCheckout()
    {
        $quote = $this->checkoutSession->getQuote();

        $quote->setCustomerId(null);
        $quote->setCustomerEmail($quote->getBillingAddress()->getEmail());
        $quote->setCustomerIsGuest(true);
        $quote->setCustomerGroupId(\Magento\Customer\Api\Data\GroupInterface::NOT_LOGGED_IN_ID);
        
        $referenceID = "638e02eee6b45c5947b40420";
        $tandymOrderID = "000999";

        $payment = $quote->getPayment();


        $additionalInformation['method_title'] = 'National Pay';
        $additionalInformation['tandym_reference_id'] = $referenceID;
        $additionalInformation['tandym_order_id'] = $tandymOrderID;
       
        $additionalInformation['tandym_status'] = "APPROVED";
        $payment->setLastTransId($referenceID);
        $payment->setTransactionId($referenceID);
        $payment->setAdditionalInformation($additionalInformation);
        $quote->setPayment($payment);

      
        $orderId = $this->cartManagement->placeOrder($quote->getId());
       
        if (!$orderId) {
            throw new CouldNotSaveException(__("Unable to place your order."));
        }
        $successURL = $this->url->getUrl("checkout/onepage/success");
        return $this->jsonHelper->jsonEncode(["checkout_url" => $successURL]);
    }
}
