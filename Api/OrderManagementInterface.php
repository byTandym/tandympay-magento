<?php
/*
 * @category    Tandym
 * @package     Tandym_Tandympay
 * @copyright   Copyright (c) Tandym (https://www.bytandym.com/)
 */
namespace Tandym\Tandympay\Api;

use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\PaymentInterface;

/**
 * Interface OrderManagementInterface
 * @package Tandym\Tandympay\Api
 */
interface OrderManagementInterface
{

    /**
     * Create Checkout
     *
     * @param int $cartId
     * @param PaymentInterface $paymentMethod
     * @param AddressInterface|null $billingAddress
     * @return string
     */
    public function createCheckout(
        $cartId,
        PaymentInterface $paymentMethod,
        AddressInterface $billingAddress = null
    );
}
