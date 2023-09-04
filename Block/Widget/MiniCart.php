<?php
/*
 * @category    Tandym
 * @package     Tandym_Tandympay
 * @copyright   Copyright (c) Tandym (https://www.bytandym.com/)
 */

namespace Tandym\Tandympay\Block\Widget;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Block\Product\View;
use Magento\Catalog\Helper\Product;
use Magento\Catalog\Model\ProductTypes\ConfigInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Pricing\Helper\Data;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\Url\EncoderInterface;
use Tandym\Tandympay\Helper\Data as TandymHelper;
use Tandym\Tandympay\Model\System\Config\Container\TandymConfigInterface;

class MiniCart extends View
{

    /**
     * @var TandymConfigInterface
     */
    protected $tandymConfig;
    /**
     * @var Data
     */
    protected $pricingHelper;
    /**
     * @var TandymHelper
     */
    private $tandymHelper;

    /**
     * AbstractWidget constructor.
     * @param Context $context
     * @param EncoderInterface $urlEncoder
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param StringUtils $string
     * @param Product $productHelper
     * @param ConfigInterface $productTypeConfig
     * @param FormatInterface $localeFormat
     * @param Session $customerSession
     * @param ProductRepositoryInterface $productRepository
     * @param PriceCurrencyInterface $priceCurrency
     * @param TandymConfigInterface $tandymConfig
     * @param Data $pricingHelper
     * @param TandymHelper $tandymHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        EncoderInterface $urlEncoder,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        StringUtils $string,
        Product $productHelper,
        ConfigInterface $productTypeConfig,
        FormatInterface $localeFormat,
        Session $customerSession,
        ProductRepositoryInterface $productRepository,
        PriceCurrencyInterface $priceCurrency,
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
            $urlEncoder,
            $jsonEncoder,
            $string,
            $productHelper,
            $productTypeConfig,
            $localeFormat,
            $customerSession,
            $productRepository,
            $priceCurrency,
            $data
        );
    }

    /**
     * Get Tandym Enabled Status
     *
     * @return string
     */
    public function getTandymEnabled()
    {
        try {
            return $this->tandymConfig->isEnabled();
        } catch (NoSuchEntityException $e) {
            return null;
        }
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
     * Get Widget Script for PDP status
     *
     * @return string
     */
    public function isWidgetEnabledForPDP()
    {
        try {
            return $this->tandymConfig->isWidgetEnabledForPDP()
                && $this->tandymConfig->isEnabled()
                && $this->getItemPrice() != '';
        } catch (NoSuchEntityException $e) {
            return false;
        }
    }

    /**
     * @return string
     */
    public function getAlignment()
    {
        return "left";
    }

    /**
     * Get Item Price
     * false changed 
     * @return float|string
     */
    public function getItemPrice()
    {
        return $this->pricingHelper->currency(
            $this->getProduct()->getFinalPrice(),
            false,
            false
        );
    }
    /**
     * Get Payment Mode
     *
     * @return string|null
     */
    public function getPaymentMode()
    {
        try {
            return $this->tandymConfig->getPaymentMode();
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }
    /**
     * Get Merchant Program Name
     *
     * @return string|null
     */
    public function getprogramName()
    {
        try {
            return $this->tandymConfig->getprogramName();
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }
    
    /**
     * Get Widget Script for Cart Page status
     *
     * @return string
     */
    public function isExpressWidgetEnabledForMiniCart()
    {
        try {
            return $this->tandymConfig->isExpressWidgetEnabledForMiniCart() && $this->tandymConfig->isExpressWidgetEnabledForCartPage()
                && $this->tandymConfig->isEnabled();
        } catch (NoSuchEntityException $e) {
            return false;
        }
    }

    /**
     * Get Express Button Image URL
     *
     * @return string|null
     */
    public function getExpressButtonImageURL()
    {
        try {
            return $this->tandymConfig->getExpressButtonImageURL();
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }
    
}
