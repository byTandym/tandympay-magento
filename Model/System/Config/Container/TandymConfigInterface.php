<?php
/*
 * @category    Tandym
 * @package     Tandym_Tandympay
 * @copyright   Copyright (c) Tandym (https://www.bytandym.com/)
 */

namespace Tandym\Tandympay\Model\System\Config\Container;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;

/**
 * Interface IdentityInterface
 * @package Tandym\Tandympay\Model\System\Config\Container
 */
interface TandymConfigInterface extends IdentityInterface
{

    /**
     * Get public key
     * @param bool $storeId
     * @param string $scope
     * @return string|null
     */
    public function getPublicKey($storeId = false, $scope = ScopeInterface::SCOPE_STORE);

    /**
     * Get private key
     * @param string $scope
     * @param bool $storeId
     * @return string|null
     */
    public function getPrivateKey($storeId = false, $scope = ScopeInterface::SCOPE_STORE);

    /**
     * Get Payment mode
     * @param string $scope
     * @param bool $storeId
     * @return string|null
     */
    public function getPaymentMode($storeId = false, $scope = ScopeInterface::SCOPE_STORE);

    /**
     * Get Program Name
     * @param string $scope
     * @param bool $storeId
     * @return string|null
     */
    public function getprogramName($storeId = false, $scope = ScopeInterface::SCOPE_STORE);

    /**
     * Get Program Description
     * @param string $scope
     * @param bool $storeId
     * @return string|null
     */
    public function getProgramDescription($storeId = false, $scope = ScopeInterface::SCOPE_STORE);

    /**
     * Get Program Logo
     * @param string $scope
     * @param bool $storeId
     * @return string|null
     */
    public function getProgramLogo($storeId = false, $scope = ScopeInterface::SCOPE_STORE);


    /**
     * Get Merchant UUID
     * @param string $scope
     * @return string|null
     */
    // public function getMerchantUUID($scope = ScopeInterface::SCOPE_STORE);

    /**
     * Get Tandym base url
     * @param bool|string $storeId
     * @param string $apiVersion
     * @param string $scope
     * @return string|null
     */
    public function getTandymBaseUrl($storeId = false, $apiVersion = TandymIdentity::API_VERSION_V2, $scope = ScopeInterface::SCOPE_STORE);

    /**
     * Get log tracker status
     * @param string $scope
     * @return bool
     */
    public function isLogTrackerEnabled($scope = ScopeInterface::SCOPE_STORE);

    /**
     * Get payment action
     * @param string $scope
     * @return string|null
     */
    public function getPaymentAction($scope = ScopeInterface::SCOPE_STORE);
    /**
     * Get default order status for authorize only
     * @param string $scope
     * @return string|null
     */
    public function getOrderStatus($scope = ScopeInterface::SCOPE_STORE);

    /**
     * Get min checkout amount
     * @param string $scope
     * @return string|null
     */
    // public function getMinCheckoutAmount($scope = ScopeInterface::SCOPE_STORE);

    /**
     * Get widget script status for PDP
     * @param string $scope
     * @return bool
     */
    public function isTandymRewardsEnabled($scope = ScopeInterface::SCOPE_STORE);

    /**
     * Get widget script status for PDP
     * @param string $scope
     * @return bool
     */
    public function isWidgetEnabledForPDP($scope = ScopeInterface::SCOPE_STORE);

    /**
     * Get widget script status for cart page
     * @param string $scope
     * @return bool
     */
    public function isWidgetEnabledForCartPage($scope = ScopeInterface::SCOPE_STORE);
    /**
     * Get Express widget script status for cart page
     * @param string $scope
     * @return bool
     */
    public function isExpressWidgetEnabledForCartPage($scope = ScopeInterface::SCOPE_STORE);
    /**
     * Get Express widget script status for mini cart
     * @param string $scope
     * @return bool
     */
    public function isExpressWidgetEnabledForMiniCart($scope = ScopeInterface::SCOPE_STORE);
    /**
     * Get Express Checkout Image Url
     * @param string $scope
     * @return bool
     */
    public function getExpressButtonImageURL($scope = ScopeInterface::SCOPE_STORE);
    /**
     * Get installment widget status for checkout page
     * @param string $scope
     * @return bool
     */
    // public function isInstallmentWidgetEnabled($scope = ScopeInterface::SCOPE_STORE);

    /**
     * Get installment widget price path
     * @param string $scope
     * @return string
     */
    // public function getInstallmentWidgetPricePath($scope = ScopeInterface::SCOPE_STORE);

    /**
     * Get tokenization status
     * @param string $scope
     * @return bool
     */
    // public function isTokenizationAllowed($scope = ScopeInterface::SCOPE_STORE);

    /**
     * Get complete url
     * @param bool|string $storeId
     * @param string $scope
     * @return bool
     */
    // public function isLogsSendingToTandymAllowed($storeId = false, $scope = ScopeInterface::SCOPE_STORE);

    /**
     * Get complete url
     * @return string
     */
    public function getCompleteUrl();

    /**
     * Get cancel url
     * @return string
     */
    public function getCancelUrl();
    /**
     * Get cart url
     * @return string
     */
    public function getCartUrl();

    /**
     * Get tokenize payment complete url
     * @return string
     */
    public function getTokenizePaymentCompleteURL();

    /**
     * Status of Settlement Reports
     * @param string $scope
     * @return bool
     */
    // public function isSettlementReportsEnabled($scope = ScopeInterface::SCOPE_STORE);

    /**
     * Get Settlement Reports range
     * @param string $scope
     * @return int
     */
    // public function getSettlementReportsRange($scope = ScopeInterface::SCOPE_STORE);

    /**
     * Check if InContext Solution is active
     * @param string $scope
     * @return bool
     */
    // public function isInContextModeEnabled($scope = ScopeInterface::SCOPE_STORE);

    /**
     * Get InContext Checkout Mode
     * @param string $scope
     * @return string
     */
    // public function getInContextMode($scope = ScopeInterface::SCOPE_STORE);

    /**
     * Get Widget Ticket Created At
     * @param string $scope
     * @return string
     */
    // public function getWidgetTicketCreatedAt($scope = ScopeInterface::SCOPE_STORE);

    /**
     * Check if current checkout is in context
     *
     * @param string $scope
     * @return bool
     */
    // public function isInContextCheckout($scope = ScopeInterface::SCOPE_STORE);

    /**
     * Check if Device is Mobile or Tablet
     *
     * @return bool
     */
    public function isMobileOrTablet();

    /**
     * Get Gateway URL
     *
     * @param string $apiVersion
     * @param string $gatewayRegion
     * @param string $scope
     * @param bool $storeId
     * @return mixed
     */
    public function getGatewayUrl(
        $apiVersion,
        $gatewayRegion = '',
        $scope = ScopeInterface::SCOPE_STORE,
        $storeId = false
    );

    /**
     * Get Widget URL
     *
     * @param string $apiVersion
     * @return mixed
     */
    public function getWidgetUrl($apiVersion);

    /**
     * Get Gateway Region
     *
     * @param string $scope
     * @param bool|string $storeId
     * @return string|null
     */
    public function getGatewayRegion($scope = ScopeInterface::SCOPE_STORE, $storeId = false);

    /**
     * Set Gateway Region
     *
     * @param int $websiteScope
     * @param int $storeScope
     * @return mixed
     */
    public function setGatewayRegion($websiteScope, $storeScope);

    /**
     * Get logo by gateway region
     *
     * @return mixed
     */
    public function getLogo();
}
