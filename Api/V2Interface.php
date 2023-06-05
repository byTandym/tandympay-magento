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
     * Refund payment by Order uuid on Failure
     *
     * @param string $url
     * @param string $orderUUID
     * @param int $amount
    
     */
    public function refundonerror($transId, $orderUUID, $amount);

    /**
     * Validate payment by Order uuid
     *
     * @param string $url
     * @param string $orderUUID
     * @param int $amount
     * @param string $currency
     * @param int $storeId
     * @return string|null
     */
    public function validatepayment($transId, $orderUUID, $amount, $currency, $storeId);
}
