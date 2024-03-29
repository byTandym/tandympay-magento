<?php
/*
 * @category    Tandym
 * @package     Tandym_Tandympay
 * @copyright   Copyright (c) Tandym (https://www.bytandym.com/)
 */

namespace Tandym\Tandympay\Api;

use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Tandym\Tandympay\Api\Data\AuthorizationInterface;
use Tandym\Tandympay\Api\Data\CustomerInterface;
use Tandym\Tandympay\Api\Data\OrderInterface;
use Tandym\Tandympay\Api\Data\SessionInterface;


interface V2Interface
{
    /**
     * Create Tandym Checkout Session
     *
     * @param string $reference
     * @param int $storeId
     * @return SessionInterface
     */
    public function createSession($reference, $storeId);

    /**
     * Refund payment by Order uuid
     *
     * @param string $url
     * @param string $orderUUID
     * @param int $amount
     * @param string $currency
     * @param int $storeId
     * @return string|null
     */
    public function refund($transId, $orderUUID, $amount, $currency, $storeId);

    /**
     * Void payment by Order uuid
     *
     * @param string $orderUUID
     * @param int $amount
     * @param string $tandymCheckoutType
     * @param string $currency
     * @param int $storeId
     * @return string|null
     */
    public function release($transId, $orderUUID, $amount, $tandymCheckoutType, $currency, $storeId);

    /**
     * Refund payment by Order uuid on Failure
     *
     * @param string $url
     * @param string $orderUUID
     * @param int $amount
     * @param string $reason
     */
    public function refundonerror($transId, $orderUUID, $amount,$reason);

    /**
     * Refund payment by Order uuid for express checkout
     *
     * @param string $url
     * @param string $orderUUID
     * @param int $amount
    
     */
    public function expressrefund($transId, $orderUUID, $amount);
    
    /**
     * Validate payment by Order uuid
     *
     * @param string $url
     * @param string $orderUUID
     * @param int $amount
     * @param string $currency
     * @param int $storeId
     * @param string $transType
     * @return string|null
     */
    public function validatepayment($transId, $orderUUID, $amount, $currency, $storeId, $transType);

    /**
     * Validate payment by Order uuid for express checkout
     *
     * @param string $url
     * @param string $orderUUID
     * @param int $amount
     * @param string $currency
     * @param int $storeId
     * @param string $transType
     * @return string|null
     */
    public function validateexpresspayment($transId, $orderUUID, $amount, $currency, $storeId, $transType);
}
