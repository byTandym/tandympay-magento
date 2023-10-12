<?php
/*
 * @category    Tandym
 * @package     Tandym_Tandympay
 * @copyright   Copyright (c) Tandym (https://www.tandym.com/)
 */

namespace Tandym\Tandympay\Controller\AbstractController;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;
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
use Tandym\Tandympay\Api\V2Interface;
use Magento\Framework\HTTP\Client\Curl;
/**
 * Class Tandym
 * @package Tandym\Tandympay\Controller\AbstractController
 */
abstract class Tandymv2 extends Action
{
    const GUEST_CART_MANAGER = "guestCartManagement";
    const CART_MANAGER = "cartManagement";
    /**
     * @var CustomerSession
     */
    protected $customerSession;
    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;
    /**
     * @var OrderFactory
     */
    protected $orderFactory;
    /**
     * @var \Tandym\Tandympay\Model\Tandym
     */
    protected $tandymModel;
    /**
     * @var OrderSender
     */
    protected $orderSender;
    /**
     * @var Data
     */
    protected $jsonHelper;
    /**
     * @var QuoteManagement
     */
    protected $quoteManagement;
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Tandym\Tandympay\Helper\Data
     */
    protected $tandymHelper;
    
    /**
     * @var CartRepositoryInterface
     */
    protected $cartRepository;
    /**
     * @var CartManagementInterface
     */
    protected $cartManagement;
    /**
     * @var GuestCartManagementInterface
     */
    protected $guestCartManagement;
    /**
     * @var QuoteIdToMaskedQuoteIdInterface
     */
    protected $quoteIdToMaskedQuoteIdInterface;
    /**
     * @var System\Config\Container\TandymConfigInterface
     */
    private $tandymConfig;
    /**
     * @var V2Interface
     */
    protected $curl;
    /**
     * @var Curl
     */
    
    protected $v2;
    /**
     * Payment constructor.
     * @param Context $context
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerSession $customerSession
     * @param CheckoutSession $checkoutSession
     * @param OrderFactory $orderFactory
     * @param \Tandym\Tandympay\Model\Tandym $tandymModel
     * @param \Tandym\Tandympay\Helper\Data $tandymHelper
     * @param JsonFactory $resultJsonFactory
     * @param Data $jsonHelper
     * @param QuoteManagement $quoteManagement
     * @param OrderSender $orderSender
     * @param CartRepositoryInterface $cartRepository
     * @param CartManagementInterface $cartManagement
     * @param GuestCartManagementInterface $guestCartManagement
     * @param QuoteIdToMaskedQuoteIdInterface $quoteIdToMaskedQuoteIdInterface
     * @param \Tandym\Tandympay\Model\System\Config\Container\TandymConfigInterface $tandymConfig
     * @param V2Interface $v2
     * @param Curl $curl
     */
    public function __construct(
        Context $context,
        CustomerRepositoryInterface $customerRepository,
        CustomerSession $customerSession,
        CheckoutSession $checkoutSession,
        OrderFactory $orderFactory,
        \Tandym\Tandympay\Model\Tandym $tandymModel,
        \Tandym\Tandympay\Helper\Data $tandymHelper,
        JsonFactory $resultJsonFactory,
        Data $jsonHelper,
        QuoteManagement $quoteManagement,
        OrderSender $orderSender,
        CartRepositoryInterface $cartRepository,
        CartManagementInterface $cartManagement,
        GuestCartManagementInterface $guestCartManagement,
        QuoteIdToMaskedQuoteIdInterface $quoteIdToMaskedQuoteIdInterface,
        \Tandym\Tandympay\Model\System\Config\Container\TandymConfigInterface $tandymConfig,
        V2Interface $v2,
        Curl $curl
    ) {
        $this->customerSession = $customerSession;
        $this->tandymHelper = $tandymHelper;
        $this->jsonHelper = $jsonHelper;
        $this->customerRepository = $customerRepository;
        $this->checkoutSession = $checkoutSession;
        $this->orderFactory = $orderFactory;
        $this->tandymModel = $tandymModel;
        $this->quoteManagement = $quoteManagement;
        $this->orderSender = $orderSender;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->cartRepository = $cartRepository;
        $this->cartManagement = $cartManagement;
        $this->guestCartManagement = $guestCartManagement;
        $this->quoteIdToMaskedQuoteIdInterface = $quoteIdToMaskedQuoteIdInterface;
        $this->tandymConfig = $tandymConfig;
        $this->v2 = $v2;
        $this->curl = $curl;
        parent::__construct($context);
    }

    /**
     * Get Order
     *
     * @return Order
     */
    protected function getOrder()
    {
        return $this->orderFactory->create()->loadByIncrementId(
            $this->checkoutSession->getLastRealOrderId()
        );
    }
}
