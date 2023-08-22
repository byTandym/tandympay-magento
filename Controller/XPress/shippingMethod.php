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

use Magento\Quote\Model\QuoteFactory;

use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\ShippingMethodManagementInterface;
use Magento\Quote\Model\Cart\ShippingMethodConverter;

class ShippingMethod extends Action implements HttpPostActionInterface
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
     * Get Shipping Methods
     * 
     * @return Json
     * 
    */
    public function execute() {

        try {

            $requestBody = json_decode($this->getRequest()->getContent());
            $requestParams = $this->getRequest()->getParams();
            $quoteId = $this->getRequest()->getParam('quoteId');
            $requestAddress = $requestBody->address;

            $tempQuote = $this->quote->create()->load($quoteId);

            // Get all visible items in cart
            $quote = $tempQuote;


            $customerShippingAddressRemote = [
                'shipping_address' => [
                    'street' => $requestAddress->street,
                    'city' =>  $requestAddress->city,
                    'country_id' => $requestAddress->country_id,
                    'region' => $requestAddress->region,
                    'region_id' => $requestAddress->region_id,
                    'postcode' => $requestAddress->postcode
                ]
            ];

            $shippingAddress = $tempQuote->getShippingAddress();
            $shippingAddress->addData($customerShippingAddressRemote['shipping_address']);
            $shippingAddress->setCollectShippingRates(true);
            $shippingAddress->collectShippingRates();
            $shippingAddress->collectShippingRates();
            $shippingRates = $shippingAddress->getGroupedAllShippingRates();

            $shippingRateOutput  = [];

            $tmpShippingRatesOutput = [];

            foreach ($shippingRates as $carrierRates) {
                foreach ($carrierRates as $rate) {
                    $shippingRateOutput[] = $rate->getData();
                    $tmpShippingRatesOutput[] = $this->shippingMethodConverter->modelToDataObject($rate, $tempQuote->getQuoteCurrencyCode());
                }
            }
        
            $shipRateOutput = [];

            foreach ($tmpShippingRatesOutput as $shippingMethod) {
                $shipRateOutput[] = [
                    "carrierTitle" => $shippingMethod->getCarrierTitle(),
                    "carrierCode" => $shippingMethod->getCarrierCode(),
                    "methodTitle" => $shippingMethod->getMethodTitle(),
                    "methodCode" => $shippingMethod->getMethodCode(),
                    "amount" => $shippingMethod->getAmount()
                ];
            }

            $result = $this->resultJsonFactory->create();
            $result->setHttpResponseCode(200);
            return $result->setData( [
                    $shippingRateOutput,
                    "shipRateOutput" => $shipRateOutput
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


