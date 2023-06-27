<?php
/*
 * @category    Tandym
 * @package     Tandym_Tandympay
 * @copyright   Copyright (c) Tandym (https://www.bytandym.com/)
 */

namespace Tandym\Tandympay\Model\Api;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\Resolver;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\StoreManagerInterface;
use Tandym\Tandympay\Helper\Data;
use Tandym\Tandympay\Helper\Util;
use Tandym\Tandympay\Model\Tandym;
use Tandym\Tandympay\Model\System\Config\Container\TandymConfigInterface;

/**
 * Class PayloadBuilder
 * @package Tandym\Tandympay\Model\Api
 */
class PayloadBuilder
{

    /**
     * @var TandymConfigInterface
     */
    private $tandymConfig;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var Data
     */
    private $tandymHelper;
    /**
     * @var Resolver
     */
    private $localeResolver;

    /**
     * PayloadBuilder constructor.
     * @param StoreManagerInterface $storeManager
     * @param TandymConfigInterface $tandymConfig
     * @param Data $tandymHelper
     * @param Resolver $localeResolver
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        TandymConfigInterface $tandymConfig,
        Data $tandymHelper,
        Resolver $localeResolver
    ) {
        $this->storeManager = $storeManager;
        $this->tandymConfig = $tandymConfig;
        $this->tandymHelper = $tandymHelper;
        $this->localeResolver = $localeResolver;
    }

    /**
     * Build Tandym Checkout Payload
     * @param Quote $quote
     * @param string $reference
     * @return array
     * @throws NoSuchEntityException
     */
    public function buildTandymCheckoutPayload($quote, $reference)
    {
        $orderPayload = [];
        $orderPayload['order'] = $this->buildOrderPayload($quote, $reference);
        $customerPayload['customer'] = $this->buildCustomerPayload($quote);
        $completeURL['complete_url'] = $this->tandymConfig->getCompleteUrl();
        $cancelURL['cancel_url'] = $this->tandymConfig->getCancelUrl();
        
        return array_merge(
            $orderPayload['order'],
            $customerPayload['customer'],
            $completeURL,
            $cancelURL,
            
        );
    }

    /**
     * Build Order Payload from Tandym Checkout Session
     *
     * @param Quote $quote
     * @param string $reference
     * @return array
     * @throws NoSuchEntityException
     */
    private function buildOrderPayload($quote, $reference)
    {

        $this->tandymHelper->logTandymActions("Order Total : " . $quote->getBaseGrandTotal());
        $testMode = $this->tandymConfig->getPaymentMode() == "sandbox"? true : false;
        $orderPayload = [
            "type" => "SALE",
            "orderid" => $quote->getReservedOrderId(),
            "quote" => $quote->getId(),
            "invoice" => $reference,
            "email" => $quote->getCustomerEmail(),
            "discount" =>$this->getPriceCents($quote->getShippingAddress()->getBaseDiscountAmount()),
            "shippingtotal" => $this->getPriceCents($quote->getShippingAddress()->getBaseShippingAmount()),
            "taxtotal" =>$this->getPriceCents($quote->getShippingAddress()->getBaseTaxAmount()), 
            "currency" => $quote->getBaseCurrencyCode(),
            "amounttotal" => $this->getPriceCents($quote->getBaseGrandTotal()), 
            "items" => $this->buildItemPayload($quote),
            "locale" => $this->localeResolver->getLocale(),
            "cancelurl" =>  $this->tandymConfig->getCancelUrl(),
            "errorurl"=> $this->tandymConfig->getCancelUrl(),
            "paymentsuccessurl"=> $this->tandymConfig->getCompleteUrl(),
            "paymentdeclinedurl"=> $this->tandymConfig->getCancelUrl(),
            "carturl"=> $this->tandymConfig->getCartUrl(),
            "testMode"=> $testMode,
            "paymentMode"=>$this->tandymConfig->getPaymentMode(),
            "checkoutMode" => "IFRAME"
        ];
        
        return $orderPayload;
    }

    /**
     * Get Price Object
     *
     * @param float $amount
     * @param string $currency
     * @return array
     */
    private function getPriceObject($amount, $currency)
    {
        return [
            "amount_in_cents" => Util::formatToCents($amount),
            "currency" => $currency
        ];
    }

     /**
     * Get Price in CENTS Object
     *
     * @param float $amount
     * @param string $currency
     * @return array
     */
    private function getPriceCents($amount)
    {
        return Util::formatToCents($amount);
    }

    /**
     * Build Customer Payload
     * @param Quote $quote
     * @return array
     */
    private function buildCustomerPayload($quote)
    {
        $billingAddress = $quote->getBillingAddress();
        
        $tokenize = false;
        return [
            "billing" => $this->buildBillingPayload($quote),
            "shipping" => $this->buildShippingPayload($quote),
        ];
    }

    /**
     * Build Billing Address Payload
     * @param Quote $quote
     * @return array
     */
    private function buildBillingPayload($quote)
    {
        $billingAddress = $quote->getBillingAddress();
        return [
            "firstname" => $billingAddress->getFirstname(),
            "lastname" => $billingAddress->getLastname(),
            "address" => $billingAddress->getStreetLine(1),
            "address2" => $billingAddress->getStreetLine(2),
            "city" => $billingAddress->getCity(),
            "state" => $billingAddress->getRegionCode(),
            "zip" => $billingAddress->getPostcode(),
            "country" => $billingAddress->getCountryId(),
            "phone" => $billingAddress->getTelephone()
        ];
    }

    /**
     * Build Shipping Address Payload
     * @param Quote $quote
     * @return array
     */
    private function buildShippingPayload($quote)
    {
        $shippingAddress = $quote->getShippingAddress();
        return [
            "firstname" => $shippingAddress->getFirstname(),
            "lastname" => $shippingAddress->getLastname(),
            "address" => $shippingAddress->getStreetLine(1),
            "address2" => $shippingAddress->getStreetLine(2),
            "city" => $shippingAddress->getCity(),
            "state" => $shippingAddress->getRegionCode(),
            "zip" => $shippingAddress->getPostcode(),
            "country" => $shippingAddress->getCountryId(),
            "phone" => $shippingAddress->getTelephone()
        ];
    }

    /**
     * Build Cart Item Payload
     * @param Quote $quote
     * @return array
     */
    private function buildItemPayload($quote)
    {
        $itemPayload = [];
        foreach ($quote->getAllVisibleItems() as $item) {
            $productName = $item->getName();
            $productSku = $item->getSku();
            $productQuantity = $item->getQty();
            
            $prdId = $item->getProduct()->getId();
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $product = $objectManager->get('Magento\Catalog\Api\ProductRepositoryInterface')->getById($prdId);
            $store = $objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore();
            
            $productImageUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getImage();
            $productUrl  = $product->getProductUrl();

            $itemData = [
                "itemid" => $productSku,
                "itemname" => $productName,
                "unitprice" => Util::formatToCents($item->getPriceInclTax()),
                "quantity" => $productQuantity,
                "itemurl" => $productUrl,
                "itemimageurl" => $productImageUrl
                
            ];
            array_push($itemPayload, $itemData);
        }
        return $itemPayload;
    }
}
