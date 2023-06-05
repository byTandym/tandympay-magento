<?php
/*
 * @category    Tandym
 * @package     Tandym_Tandympay
 * @copyright   Copyright (c) Tandym (https://www.bytandym.com/)
 */

namespace Tandym\Tandympay\Model\System\Config\Container;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\ScopeInterface as StoreScopeInterface;
use Zend_Http_UserAgent_Mobile;

/**
 * Class TandymIdentity
 * @package Tandym\Tandympay\Model\System\Config\Container
 */
class TandymIdentity extends Container implements TandymConfigInterface
{
    const PROD_MODE = 'live';
    const SANDBOX_MODE = 'sandbox';

    

    const API_VERSION_V1 = 'v1';
    const API_VERSION_V2 = 'v2';

    const XML_PATH_PUBLIC_KEY = 'payment/tandympay/public_key';
    const XML_PATH_PAYMENT_ACTIVE = 'payment/tandympay/active';
    const XML_PATH_PAYMENT_MODE = 'payment/tandympay/payment_mode';
    const XML_PATH_PRIVATE_KEY = 'payment/tandympay/private_key';
    const XML_PATH_PROGRAM_NAME = 'payment/tandympay/title';
    const XML_PATH_PROGRAM_DESCRIPTION = 'payment/tandympay/program_description';
    
    const XML_PATH_PROGRAM_LOGO = 'payment/tandympay/program_logo';
    
    const XML_PATH_GATEWAY_REGION = 'payment/tandympay/gateway_region';
    
    const XML_PATH_PAYMENT_ACTION = 'payment/tandympay/payment_action';
    
    const XML_PATH_WIDGET_PDP = 'payment/tandympay/widget_pdp';
    const XML_PATH_WIDGET_CART = 'payment/tandympay/widget_cart';
    


    
    

    const XML_PATH_LOG_TRACKER = 'payment/tandympay/log_tracker';
    

    const GATEWAY_URL = "https://%sgateway.%s/%s";
    const TANDYM_DOMAIN = "%standym.%s";

    const WIDGET_URL = "https://widget.%s/%s";

    private static $supportedGatewayRegions = [
        'US' => 'https://assets.platform.bytandym.com/mapps-assets/bytandym-logo.png'
    ];

    /**
     * @inheritdoc
     */
    public function isEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_PAYMENT_ACTIVE,
            ScopeInterface::SCOPE_STORE,
            $this->getStore()->getStoreId()
        );
    }
    /**
     * @inheritdoc
     */
    public function getProgramName($storeId = false, $scope = ScopeInterface::SCOPE_STORE)
    {
        $storeId = $storeId ?: $this->getStore()->getStoreId();
        return $this->getConfigValue(
            self::XML_PATH_PROGRAM_NAME,
            $storeId,
            $scope
        );
    }
    /**
     * @inheritdoc
     */
    public function getProgramDescription($storeId = false, $scope = ScopeInterface::SCOPE_STORE)
    {
        $storeId = $storeId ?: $this->getStore()->getStoreId();
        return $this->getConfigValue(
            self::XML_PATH_PROGRAM_DESCRIPTION,
            $storeId,
            $scope
        );
    }

     /**
     * @inheritdoc
     */
    public function getProgramLogo($storeId = false, $scope = ScopeInterface::SCOPE_STORE)
    {
        $storeId = $storeId ?: $this->getStore()->getStoreId();
        return $this->getConfigValue(
            self::XML_PATH_PROGRAM_LOGO,
            $storeId,
            $scope
        );
    }

    
    /**
     * @inheritdoc
     */
    public function getPublicKey($storeId = false, $scope = ScopeInterface::SCOPE_STORE)
    {
        $storeId = $storeId ?: $this->getStore()->getStoreId();
        return $this->getConfigValue(
            self::XML_PATH_PUBLIC_KEY,
            $storeId,
            $scope
        );
    }

    /**
     * @inheritdoc
     */
    public function getPrivateKey($storeId = false, $scope = ScopeInterface::SCOPE_STORE)
    {
        $storeId = $storeId ?: $this->getStore()->getStoreId();
        return $this->getConfigValue(
            self::XML_PATH_PRIVATE_KEY,
            $storeId,
            $scope
        );
    }

    /**
     * @inheritdoc
     */
    public function getPaymentMode($storeId = false, $scope = ScopeInterface::SCOPE_STORE)
    {
        $storeId = $storeId ?: $this->getStore()->getStoreId();
        return $this->getConfigValue(
            self::XML_PATH_PAYMENT_MODE,
            $storeId,
            $scope
        );
    }

    
    /**
     * @inheritdoc
     */
    public function getTandymBaseUrl($storeId = false, $apiVersion = TandymIdentity::API_VERSION_V2, $scope = ScopeInterface::SCOPE_STORE)
    {
        $gatewayRegion = $this->getGatewayRegion($scope, $storeId) ?: $this->defaultRegion();
        return $this->getGatewayUrl($apiVersion, $gatewayRegion, $scope, $storeId);
    }

    /**
     * @inheritdoc
     */
    public function isLogTrackerEnabled($scope = ScopeInterface::SCOPE_STORE)
    {
        return $this->getConfigValue(
            self::XML_PATH_LOG_TRACKER,
            $this->getStore()->getStoreId(),
            $scope
        );
    }

    /**
     * @inheritdoc
     */
    public function getPaymentAction($scope = ScopeInterface::SCOPE_STORE)
    {
        return $this->getConfigValue(
            self::XML_PATH_PAYMENT_ACTION,
            $this->getStore()->getStoreId(),
            $scope
        );
    }

   
    /**
     * @inheritdoc
     */
    public function isWidgetEnabledForPDP($scope = ScopeInterface::SCOPE_STORE)
    {
        return $this->getConfigValue(
            self::XML_PATH_WIDGET_PDP,
            $this->getStore()->getStoreId(),
            $scope
        );
    }

    /**
     * @inheritdoc
     */
    public function isWidgetEnabledForCartPage($scope = ScopeInterface::SCOPE_STORE)
    {
        return $this->getConfigValue(
            self::XML_PATH_WIDGET_CART,
            $this->getStore()->getStoreId(),
            $scope
        );
    }

    

    /**
     * @inheritdoc
     */
    public function getCompleteUrl()
    {
        return $this->urlBuilder
            ->getUrl(
                "tandym/payment/complete/",
                ['_secure' => true]
            );
        
    }

    /**
     * @inheritdoc
     */
    public function getCancelUrl()
    {
        return $this->urlBuilder
            ->getUrl(
                "tandym/payment/cancel/",
                ['_secure' => true]
            );

        
    }

    /**
     * @inheritdoc
     */
    public function getTokenizePaymentCompleteURL()
    {
        return $this->urlBuilder->getUrl(
            "tandym/tokenize/paymentComplete",
            [
                '_secure' => true
            ]
        );
    }

    
    /**
     * @inheritdoc
     */
    public function isMobileOrTablet()
    {
        $userAgent = $this->httpHeader->getHttpUserAgent();
        return Zend_Http_UserAgent_Mobile::match($userAgent, $_SERVER);
    }

    /**
     * Get Tandym domain
     *
     * @param string $gatewayRegion
     * @return string
     */
    private function getTandymDomain($gatewayRegion = '')
    {
        $region = $gatewayRegion === $this->defaultRegion() || $gatewayRegion === 'IN' ? '' : "$gatewayRegion.";
        $regionTld = $gatewayRegion === 'IN' ? 'in' : 'com';
        return sprintf(self::TANDYM_DOMAIN, strtolower($region), $regionTld);
    }

    /**
     * @inheritDoc
     */
    public function getGatewayUrl(
        $apiVersion,
        $gatewayRegion = '',
        $scope = ScopeInterface::SCOPE_STORE,
        $storeId = false
    ) {
        $tandymDomain = $this->getTandymDomain($gatewayRegion);
        $env = $this->getPaymentMode($storeId, $scope) === self::SANDBOX_MODE ? 'sandbox.' : '';
        return sprintf(self::GATEWAY_URL, $env, $tandymDomain, $apiVersion);
    }

    /**
     * @inheritDoc
     */
    public function getWidgetUrl($apiVersion)
    {
        $tandymDomain = $this->getTandymDomain($this->getGatewayRegion());
        return sprintf(self::WIDGET_URL, $tandymDomain, $apiVersion);
    }

    /**
     * @inheritdoc
     */
    public function getGatewayRegion($scope = ScopeInterface::SCOPE_STORE, $storeId = false)
    {
        $storeId = $storeId ?: $this->getStore()->getStoreId();
        $region = $this->getConfigValue(
            self::XML_PATH_GATEWAY_REGION,
            $storeId,
            $scope
        );
        return $region ?: $this->defaultRegion();
    }

    /**
     * @inheritDoc
     */
    public function setGatewayRegion($websiteScope, $storeScope)
    {
        $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
        $scopeId = 0;
        if ($websiteScope) {
            $scope = StoreScopeInterface::SCOPE_WEBSITES;
            $scopeId = $websiteScope;
        } elseif ($storeScope) {
            $scope = StoreScopeInterface::SCOPE_STORES;
            $scopeId = $storeScope;
        }

        $gatewayRegion = '';
        foreach (array_keys(self::$supportedGatewayRegions) as $region) {
            $ok = $this->validateAPIKeys($region, $scope, $scopeId);
            if ($ok) {
                $gatewayRegion = $region;
                break;
            }
        }
        if (!$gatewayRegion) {
            $this->tandymHelper->logTandymActions("Gateway Region not found");
            throw new AuthenticationException(__('Unable to authenticate.'));
        }

        $this->tandymHelper->logTandymActions(sprintf("Gateway Region: %s", $gatewayRegion));
        $this->resourceConfig->saveConfig(
            TandymIdentity::XML_PATH_GATEWAY_REGION,
            $gatewayRegion,
            $scope,
            $scopeId
        );
    }

    /**
     * @inheritDoc
     */
    public function getLogo()
    {
        return self::$supportedGatewayRegions[$this->getGatewayRegion()];
    }

    /**
     * Get default region
     *
     * @return string|null
     */
    private function defaultRegion()
    {
        if (function_exists('array_key_first')) {
            return array_key_first(self::$supportedGatewayRegions);
        }

        foreach (self::$supportedGatewayRegions as $key => $value) {
            return $key;
        }

        return 'US';
    }
}
