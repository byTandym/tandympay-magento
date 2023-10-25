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
use Magento\Quote\Model\Quote\TotalsCollector;

use \Magento\Customer\Model\Session as CustomerSession;


class ShippingMethod extends Action implements HttpPostActionInterface
{
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
        \Magento\Sales\Model\Service\OrderService $orderService ,
        CheckoutSession $checkoutSession,
        ShippingMethodManagementInterface $shippingMethodManagement,
        ShippingMethodConverter $shippingMethodConverter,
        \Tandym\Tandympay\Helper\Data $tandymHelper,
        TotalsCollector $totalsCollector,
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
        $this->checkoutSession = $checkoutSession;
        $this->shippingMethodManagement = $shippingMethodManagement;
        $this->shippingMethodConverter = $shippingMethodConverter;
        $this->totalsCollector = $totalsCollector;
        $this->tandymHelper = $tandymHelper;
        $this->customerSession = $customerSession;
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

            $this->tandymHelper->logTandymActions("TDM-XCO: Getting Available Shipping Methods for QuoteId: ".$quoteId);

            $tempQuote = $this->quote->create()->load($quoteId);

            $customerId = $tempQuote->getCustomerId();
            
            if ($customerId) {
                $this->customerSession->loginById($customerId);
            }

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
            $this->totalsCollector->collectAddressTotals($tempQuote, $shippingAddress);
            
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
                    "code" => $shippingMethod->getCarrierCode()."_".$shippingMethod->getMethodCode(),
                    "carrier" => $shippingMethod->getCarrierCode(),
                    "carrier_title" => $shippingMethod->getCarrierTitle(),
                    "method" => $shippingMethod->getMethodCode(),
                    "method_title" => $shippingMethod->getMethodTitle(),
                    "method_description" => "",
                    "price" => $shippingMethod->getAmount()
                ];
            }

            $this->tandymHelper->logTandymActions("TDM-XCO: ShippingMethod List ".json_encode($shipRateOutput));

            $result = $this->resultJsonFactory->create();
            $result->setHttpResponseCode(200);
            return $result->setData( [
                    "shippingRates" => $shippingRateOutput,
                    "shipRateOutput" => $shipRateOutput
                ]
            );
        } catch (Exception $e) {
            $this->tandymHelper->logTandymActions("TDM-XCO: ShippingMethod Exception ".$e->getMessage());
            $result = $this->resultJsonFactory->create();
            $result->setHttpResponseCode(400);
            return $result->setData([
                'error' => $e->getMessage()
            ]);
        }
    }
}


