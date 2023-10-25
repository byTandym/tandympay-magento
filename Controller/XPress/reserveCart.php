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

use \Magento\Customer\Model\Session as CustomerSession;

class reserveCart extends Action implements HttpPostActionInterface
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
        $this->tandymHelper = $tandymHelper;
        $this->customerSession = $customerSession;
        parent::__construct($context);
    }
   
    /**
     * Get a reserverd Order ID
     * 
     * @return Json
     * 
    */
    public function execute() {
        
        $_SESSION["tandym_rewards"] = 0;
        
        $maskedHashId = $this->getRequest()->getParam('cartId');
        $this->tandymHelper->logTandymActions("TDM-XCO: Initial Request from Tandym Express Checkout");

        $quoteId = "";
        try {
            $quoteId = $this->maskedQuoteIdToQuoteId->execute($maskedHashId);
            $this->tandymHelper->logTandymActions("TDM-XCO: Guest QuoteId:".$quoteId);
        } catch (NoSuchEntityException $e) {
            $quoteId = $maskedHashId;
            $this->tandymHelper->logTandymActions("TDM-XCO: Customer QuoteId:".$quoteId);
        }
    
        try {
            $tempQuote = $this->quote->create()->load($quoteId);

            $customerId = $tempQuote->getCustomerId();
            if ($customerId) {
                $this->customerSession->loginById($customerId);
            }
            
            $tempQuote->reserveOrderId();
            
            $tempQuote->save();

            $quoteData  = $tempQuote->getData();
            if (isset($quoteData["subtotal"])) {

                $result = $this->resultJsonFactory->create();
                $result->setHttpResponseCode(200);
                $this->tandymHelper->logTandymActions("TDM-XCO: Reserved Order: ".$quoteData["reserved_order_id"]);
                return $result->setData([
                    'quoteId' => strval($quoteId),
                    'reserveOrderId' => $quoteData["reserved_order_id"],
                    'subtotal' => floatval($quoteData["subtotal"]),
                    'subtotal_with_discount' => floatval($quoteData["subtotal_with_discount"]),
                    'discount_on_subtotal'=> floatval($quoteData["subtotal"]) - floatval($quoteData["subtotal_with_discount"]),
                    'grand_total' => floatval($quoteData["grand_total"]),
                    'other_amount_total' => floatval($quoteData["grand_total"]) - floatval($quoteData["subtotal_with_discount"])
                    //,
                    //'quoteData' => $quoteData
                ]);
            } else {
                $result = $this->resultJsonFactory->create();
                $result->setHttpResponseCode(400);
                $this->tandymHelper->logTandymActions("TDM-XCO: Error -> QuoteID: ".$quoteId);
                return $result->setData([
                    'status' => "error - not found",
                    'quoteId' => strval($quoteId)
                ]);
            }
           
        } catch (Exception $e) {
            $this->tandymHelper->logTandymActions("TDM-XCO: ReserveCart - Exception -> ".$e->getMessage());
            $result = $this->resultJsonFactory->create();
            $result->setHttpResponseCode(400);
            return $result->setData([
                'error' => $e->getMessage()
            ]);
        }
    }
}


