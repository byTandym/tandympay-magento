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

class getTotals extends Action implements HttpPostActionInterface
{

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
        ShippingMethodConverter $shippingMethodConverter,
        \Tandym\Tandympay\Helper\Data $tandymHelper
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
        $this->tandymHelper = $tandymHelper;
        parent::__construct($context);
    }
   
    /**
     * Place Order
     * 
     * @return Json
     * 
    */
    public function execute() {

        try {

            $requestBody = json_decode($this->getRequest()->getContent());
            $requestParams = $this->getRequest()->getParams();
            $quoteId = $this->getRequest()->getParam('quoteId');

           $this->tandymHelper->logTandymActions("TDM-XCO: Getting Cart Total for QuoteId: ".$quoteId);

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

            $shippingAddress = $tempQuote->getShippingAddress();

            $shippingAddress->addData($customerBillingAddressRemote['billing_address']);

            $billingAddress = $tempQuote->getBillingAddress();

            $billingAddress->addData($customerShippingAddressRemote['shipping_address']);

            $shippingAddress->setCollectShippingRates(true);

            $shippingAddress->setShippingMethod($requestshippingCarrierCode."_".$requestshippingMethodCode)->save(); 

            $tempQuote->collectTotals()->save();


            $taxAmount =  $tempQuote->getShippingAddress()->getData('tax_amount');

            $totals = $tempQuote->getTotals();
            $tax = (isset($totals['tax'])) ? $totals['tax']->getValue(): 0;

            $result = $this->resultJsonFactory->create();
            $result->setHttpResponseCode(200);
            
            $shippingTotal = $shippingAddress->getData();
            $grandTotal = $shippingTotal["grand_total"];
            $subtotal = $shippingTotal["subtotal"];
            $tax_amount = $shippingTotal["tax_amount"];
            $shippingAmount = $shippingTotal["shipping_amount"];
            $discount_amount = $shippingTotal["discount_amount"];

            $dataToSend = [
                "grandTotal" =>  $grandTotal,
                "subTotal" => $subtotal,
                "taxAmount" => $tax_amount,
                "shippingAmount" => $shippingAmount,
                "discountAmount" => $discount_amount,
                "mageShipping" => $shippingAddress->getData(),
                'quoteData' => $tempQuote->getData()
            ];

            $this->tandymHelper->logTandymActions("TDM-XCO: Cart Total : ".json_encode($dataToSend));

            return $result->setData([
                "grandTotal" =>  $grandTotal,
                "subTotal" => $subtotal,
                "taxAmount" => $tax_amount,
                "shippingAmount" => $shippingAmount,
                "discountAmount" => $discount_amount,
                "mageShipping" => $shippingAddress->getData(),
                'quoteData' => $tempQuote->getData()
            ]
                
            );
        } catch (Exception $e) {

            $this->tandymHelper->logTandymActions("TDM-XCO: Cart Total Exception -> ".$e->getMessage());

            $result = $this->resultJsonFactory->create();
            $result->setHttpResponseCode(400);
            return $result->setData([
                'error' => $e->getMessage()
            ]);
        }
    }
}


