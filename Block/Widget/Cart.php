<?php
/*
 * @category    Tandym
 * @package     Tandym_Tandympay
 * @copyright   Copyright (c) Tandym (https://www.bytandym.com/)
 */

namespace Tandym\Tandympay\Block\Widget;

use Magento\Catalog\Model\ResourceModel\Url;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\Helper\Data;
use Magento\Framework\View\Element\Template\Context;
use Tandym\Tandympay\Model\System\Config\Container\TandymConfigInterface;
use Tandym\Tandympay\Helper\Data as TandymHelper;

/**
 * Class Cart
 * @package Tandym\Tandympay\Block\Widget
 */
class Cart extends \Magento\Checkout\Block\Cart
{

    /**
     * @var TandymConfigInterface
     */
    private $tandymConfig;
    /**
     * @var Data
     */
    private $pricingHelper;
    /**
     * @var TandymHelper
     */
    private $tandymHelper;

    /**
     * Cart constructor.
     * @param Context $context
     * @param CustomerSession $customerSession
     * @param CheckoutSession $checkoutSession
     * @param Url $catalogUrlBuilder
     * @param \Magento\Checkout\Helper\Cart $cartHelper
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param TandymConfigInterface $tandymConfig
     * @param Data $pricingHelper
     * @param TandymHelper $tandymHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        CheckoutSession $checkoutSession,
        Url $catalogUrlBuilder,
        \Magento\Checkout\Helper\Cart $cartHelper,
        \Magento\Framework\App\Http\Context $httpContext,
        TandymConfigInterface $tandymConfig,
        Data $pricingHelper,
        TandymHelper $tandymHelper,
        array $data = []
    ) {
        $this->tandymConfig = $tandymConfig;
        $this->pricingHelper = $pricingHelper;
        $this->tandymHelper = $tandymHelper;
        parent::__construct(
            $context,
            $customerSession,
            $checkoutSession,
            $catalogUrlBuilder,
            $cartHelper,
            $httpContext,
            $data
        );
    }


    /**
     * Get Widget Type
     *
     * @return string
     */
    public function getWidgetType()
    {
        return "standard";
    }

    /**
     * Get Widget Script for Cart Page status
     *
     * @return string
     */
    public function isWidgetEnabledForCartPage()
    {
        try {
            return $this->tandymConfig->isWidgetEnabledForCartPage()
                && $this->tandymConfig->isEnabled()
                && $this->getGrandTotal() != '';
        } catch (NoSuchEntityException $e) {
            return false;
        }
    }

    /**
     * @return string
     */
    public function getGrandTotal()
    {
        $totals = $this->getTotals();
        $firstTotal = reset($totals);
        if ($firstTotal) {
            return $this->pricingHelper->currency(
                $firstTotal->getAddress()->getBaseGrandTotal(),
                false,
                false
            );
        }
        return '';
    }

    /**
     * @return string
     */
    public function getAlignment()
    {
        return "right";
    }

    /**
     * Get Merchant API Key
     *
     * @return string|null
     */
    public function getPublicKey()
    {
        try {
            return $this->tandymConfig->getPublicKey();
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }
   
}
