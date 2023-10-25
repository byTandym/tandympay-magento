<?php

namespace Tandym\Tandympay\Controller\XPress;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Json\Helper\Data;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\GuestCartManagementInterface;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Model\QuoteManagement;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\OrderFactory;
use Exception;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Api\Data\CartInterface;
use Tandym\Tandympay\Model\System\Config\Container\TandymConfigInterface;
use Magento\Framework\HTTP\Client\Curl;
use \Magento\Customer\Model\Session as CustomerSession;

class placeOrder extends Action implements HttpPostActionInterface
{

    const TANDYM_EXPRESS_REWARDS_URL_PROD = "https://plugin.api.platform.poweredbytandym.com/express/rewards";
    const TANDYM_EXPRESS_REWARDS_URL_STAGING = "https://stagingapi.platform.poweredbytandym.com/express/rewards";
    
    
    /**
     * @var CustomerSession
     */
    protected $customerSession;

    public function __construct(
        Context $context,
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\Data\Form\FormKey $formkey,
        QuoteFactory $quote,
        CartRepositoryInterface $quoteRepository,
        \Magento\Quote\Model\QuoteManagement $quoteManagement,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        JsonFactory $resultJsonFactory,
        \Magento\Sales\Model\Service\OrderService $orderService,
        \Tandym\Tandympay\Helper\Data $tandymHelper,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        TandymConfigInterface $tandymConfig,
        Data $jsonHelper, 
        Curl $curl,
        CustomerSession $customerSession
    ) {
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
        $this->_storeManager = $storeManager;
        $this->_product = $product;
        $this->_formkey = $formkey;
        $this->quote = $quote;
        $this->quoteRepository = $quoteRepository;
        $this->quoteManagement = $quoteManagement;
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->orderService = $orderService;
        $this->tandymHelper = $tandymHelper;
        $this->orderSender = $orderSender;
        $this->tandymConfig = $tandymConfig;
        $this->jsonHelper = $jsonHelper;
        $this->curl = $curl;
        $this->customerSession = $customerSession;
        parent::__construct($context);
    }

    /**
     * Place Order after Tandym Payment is successful
     * 
     * @return Json
     * 
    */
    public function execute() {

        try {
            $requestBody = json_decode($this->getRequest()->getContent());

            //REWARDS APPLIED CALL
            $_SESSION["tandym_rewards"] = 0; //-ve value to set 
            $apiKey = $this->tandymConfig->getPublicKey();
            $apiSecret = $this->tandymConfig->getPrivateKey();
            $url = $this->tandymConfig->getPaymentMode() == "live" ? self::TANDYM_EXPRESS_REWARDS_URL_PROD : self::TANDYM_EXPRESS_REWARDS_URL_STAGING;
            $payload = [
                'transaction_receipt' => $requestBody->transaction_receipt,
                "testMode" =>  $this->tandymConfig->getPaymentMode() == "live" ? false : true
            ];

            $this->curl->addHeader("Content-Type", "application/json");
            $this->curl->addHeader("apikey", $apiKey);
            $this->curl->addHeader("secret", $apiSecret);

            $this->curl->post($url, json_encode($payload));
            $this->tandymHelper->logTandymActions("Request sent to TANDYM Middleware for Rewards Applied");
            $this->tandymHelper->logTandymActions($url);
            $this->tandymHelper->logTandymActions($payload);
            $responsefromtdm = $this->curl->getBody();
            $statusCode = $this->curl->getStatus();

            $this->tandymHelper->logTandymActions("Response from Tamdym Middleware with Response Status Code: $statusCode");
            $this->tandymHelper->logTandymActions($responsefromtdm);
            $_SESSION["tandym_rewards"] = 0;
            $tandymRewardsApplied = 0;

            if ($statusCode == "200") {
                $body = $this->jsonHelper->jsonDecode($responsefromtdm);
                
                $tandymRewardsApplied =  isset($body['rewardsApplied']) && $body['rewardsApplied'] ? $body['rewardsApplied'] : 0;

                $_SESSION["tandym_rewards"] = -1 * $tandymRewardsApplied;
            }

            //END REWARDS APPLIED CALL

            $requestshippingAddress = $requestBody->shipping_address;
            $requestbillingAddress = $requestBody->billing_address;

            $customer_firstname = $requestBody->customer_first_name;
            $customer_lastname = $requestBody->customer_last_name;
            $customer_email = str_replace("+", "", $requestBody->customer_email);
            $customer_telephone = $requestBody->phone;
            $tandym_receipt= $requestBody->transaction_receipt;
            
            $quoteId = $this->getRequest()->getParam('quoteId');

            $this->tandymHelper->logTandymActions("TDM-XCO: Place Order Request from Tandym for QuoteId: ".$quoteId);

            $tempQuote = $this->quote->create()->load($quoteId);

            $customerId = $tempQuote->getCustomerId();
            
            if ($customerId) {
                $this->customerSession->loginById($customerId);
            }
            
            $quoteData = $tempQuote->getData();

            
            $tempQuote->setCustomerFirstname($customer_firstname);
            $tempQuote->setCustomerLastname($customer_lastname);
            $tempQuote->setCustomerEmail($customer_email);

            $isGuest = $quoteData["customer_is_guest"] == "1" ? true : false;

            $tempQuote->setCustomerIsGuest($isGuest);
            
            $customerBillingAddressRemote = [
                'billing_address' => [
                    'firstname'    => $customer_firstname,
                    'lastname'     => $customer_lastname,
                    'telephone' => $customer_telephone,
                    'street' => $requestbillingAddress->street,
                    'city' =>  $requestbillingAddress->city,
                    'country_id' => $requestbillingAddress->country_id,
                    'region' => $requestbillingAddress->region,
                    'region_id' => $requestbillingAddress->region_id,
                    'postcode' => $requestbillingAddress->postcode
                ]
            ];

            $customerShippingAddressRemote = [
                'shipping_address' => [
                    'firstname'    => $customer_firstname,
                    'lastname'     => $customer_lastname,
                    'telephone' => $customer_telephone,
                    'street' => $requestshippingAddress->street,
                    'city' =>  $requestshippingAddress->city,
                    'country_id' => $requestshippingAddress->country_id,
                    'region' => $requestshippingAddress->region,
                    'region_id' => $requestshippingAddress->region_id,
                    'postcode' => $requestshippingAddress->postcode
                ]
            ];

            $tempQuote->getBillingAddress()->addData($customerBillingAddressRemote['billing_address']);
            $tempQuote->getShippingAddress()->addData($customerShippingAddressRemote['shipping_address']);
    
            $tempQuote->setPaymentMethod('tandympay'); 
            $tempQuote->setInventoryProcessed(false); 
            $tempQuote->save(); 
    
          // Set Sales Order Payment
            $tempQuote->getPayment()->importData(['method' => 'tandympay']);
    
            $payment = $tempQuote->getPayment();
            
            $additionalInformation['tandym_rewards_applied'] = $_SESSION["tandym_rewards"];
            $additionalInformation['tandym_order_type'] = 'v2';
            $additionalInformation['tandym_reference_id'] = $tandym_receipt;
            $additionalInformation['tandym_original_order_uuid'] = $quoteData["reserved_order_id"];
            $additionalInformation['tandym_checkout_type'] = "EXPRESS";
            $additionalInformation['tandym_status'] = "APPROVED";

            $payment->setAdditionalInformation($additionalInformation);

            $tempQuote->setPayment($payment);
    
            $tempQuote->collectTotals()->save();

            $order = $this->quoteManagement->submit($tempQuote);
            
            $this->tandymHelper->logTandymActions("TDM-XCO: Order Created from Tandym for QuoteId: ".$quoteId." - Order#: ".$quoteData["reserved_order_id"]);
            
            if($order){
                $this->orderSender->send($order);
                $orderData = $order->getData();
                $result = $this->resultJsonFactory->create();
                $result->setHttpResponseCode(200);
                return $result->setData([
                    'cartID' => $quoteId,
                    'orderID' => $quoteData["reserved_order_id"],
                    'orderData' => $orderData
                ]);
            } else {
                $this->tandymHelper->logTandymActions("TDM-XCO: Order Creation Failed Error -> QuoteID: ".$quoteId);
                $result = $this->resultJsonFactory->create();
                $result->setHttpResponseCode(400);
                return $result->setData([
                    'error' => "Error Occured at Order Creation - Order Failure"
                ]);
            }
        }  catch (Exception $e) {
            $this->tandymHelper->logTandymActions("TDM-XCO: Cart Total Exception -> ".$e->getMessage());
            $result = $this->resultJsonFactory->create();
                $result->setHttpResponseCode(400);
            return $result->setData([
                'error' => $e->getMessage()
            ]);
        }

        $_SESSION["tandym_rewards"] = 0;

    }
}


