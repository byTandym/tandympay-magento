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
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\QuoteIdMask;


use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Checkout\Model\Session as CheckoutSession;


//used for getting quote data from id
use Magento\Quote\Model\QuoteFactory;

use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\ShippingMethodManagementInterface;
use Magento\Quote\Model\Cart\ShippingMethodConverter;

class cartTotal extends Action implements HttpPostActionInterface
{

    protected $maskedQuoteIdToQuoteId;
    protected $_storeManager;
    protected $_product;
    protected $_formkey;
    protected $quote;
    protected $quoteRepository;
    protected $quoteManagement;
    protected $customerFactory;
    protected $customerRepository;
    protected $resultJsonFactory;
    protected $orderService;
    protected $checkoutSession;
    protected $shippingMethodManagement;
    protected $shippingMethodConverter;
    
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
        \Magento\Sales\Model\Service\OrderService $orderService ,
        CheckoutSession $checkoutSession,
        ShippingMethodManagementInterface $shippingMethodManagement,
        ShippingMethodConverter $shippingMethodConverter
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
        $this->checkoutSession = $checkoutSession;
        $this->shippingMethodManagement = $shippingMethodManagement;
        $this->shippingMethodConverter = $shippingMethodConverter;
        parent::__construct($context);
    }
   
    /**
     * Get the Final Cart Total for Payment Processing
     * 
     * @return Json
     * 
    */
    public function execute() {

        try {

            $requestBody = json_decode($this->getRequest()->getContent());
            $requestParams = $this->getRequest()->getParams();
            $quoteId = $this->getRequest()->getParam('quoteId');

           
            $requestshippingAddress = $requestBody->addressInformation->shipping_address;
            $requestbillingAddress = $requestBody->addressInformation->billing_address;
            $requestshippingMethodCode =  $requestBody->addressInformation->shipping_method_code;
            $requestshippingCarrierCode =  $requestBody->addressInformation->shipping_carrier_code;


            $tempQuote = $this->quote->create()->load($quoteId);

            // Get all visible items in cart
            $quote = $tempQuote;

            $customerBillingAddressRemote = [
                'billing_address' => [
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

            //$tempQuote->save();

            $shippingAddress = $tempQuote->getShippingAddress();

            $shippingAddress->setCollectShippingRates(true)
                        ->collectShippingRates()
                        ->setShippingMethod($requestshippingCarrierCode."_".$requestshippingMethodCode); 

            //$shippingAddress->collectShippingRates();
            // $shippingAddress->setCollectShippingRates(true)->collectShippingRates();
            // $shippingRates =  $shippingAddress->setCollectShippingRates(true)->collectShippingRates()->getGroupedAllShippingRates();

            // $shippingRateOutput  = [];
            // foreach ($shippingRates as $carrierRates) {
            //     foreach ($carrierRates as $rate) {
            //         //$shippingRateOutput[] = $this->shippingMethodConverter->modelToDataObject($rate, $tempQuote->getQuoteCurrencyCode());
            //         $shippingRateOutput[] = $rate->getData();
            //     }
            // }
            //$tempQuote->save();
            $tempQuote->collectTotals()->save();
            $taxAmount =  $tempQuote->getShippingAddress()->getData('tax_amount');

            $totals = $tempQuote->getTotals();
            $tax = (isset($totals['tax'])) ? $totals['tax']->getValue(): 0;

            $result = $this->resultJsonFactory->create();
            $result->setHttpResponseCode(200);


            // grandTotal:magentoShippingMethodInfo.totals.base_grand_total, 
            // subTotal:magentoShippingMethodInfo.totals.base_subtotal,
            // taxAmount:magentoShippingMethodInfo.totals.base_tax_amount,
            // shippingAmount:magentoShippingMethodInfo.totals.shipping_incl_tax,
            // discountAmount:magentoShippingMethodInfo.totals.base_discount_amount   
            
            $shippingTotal = $shippingAddress->getData();
            $grandTotal = $shippingTotal["grand_total"];
            $subtotal = $shippingTotal["subtotal"];
            $tax_amount = $shippingTotal["tax_amount"];
            $shippingAmount = $shippingTotal["shipping_amount"];
            $discount_amount = $shippingTotal["discount_amount"];

            return $result->setData([
                //'QuoteData' => $tempQuote->getData(),
                // "billingAddress" => $requestbillingAddress,
                // "shippingAddress" => $requestshippingAddress,
                // "methodCode" => $requestshippingMethodCode,
                // "carrierCode" => $requestshippingCarrierCode,
                // "carrier_method" =>$requestshippingCarrierCode."_".$requestshippingMethodCode,
                "grandTotal" =>  $grandTotal,
                "subTotal" => $subtotal,
                "taxAmount" => $tax_amount,
                "shippingAmount" => $shippingAmount,
                "discountAmount" => $discount_amount,
                
                // "taxAmount" => $taxAmount,
                // "tax" => $tax,
                //"mageShipping" => $shippingAddress->getData(),
            ]
                
            );
        } catch (Exception $e) {

            $result = $this->resultJsonFactory->create();
            $result->setHttpResponseCode(400);
            return $result->setData([
                'error' => $e->getMessage()
            ]);
        }
    }
}


