<?php
/*
 * @category    Tandym
 * @package     Tandym_Tandympay
 * @copyright   Copyright (c) Tandym (https://www.bytandym.com/)
 */

namespace Tandym\Tandympay\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;
use Tandym\Tandympay\Helper\Data;

/**
 * Class CheckoutValidator
 * @package Tandym\Tandympay\Model\Order
 */
class CheckoutValidator
{
    /**
     * @var string[]
     */
    private $requiredFields = [
        "firstname",
        "lastname",
        "street",
        "city",
        "region_id",
        "postcode",
        "country_id",
        "telephone"
    ];
    /**
     * @var Data
     */
    private $tandymHelper;

    /**
     * AddressValidator constructor.
     * @param Data $tandymHelper
     */
    public function __construct(Data $tandymHelper)
    {
        $this->tandymHelper = $tandymHelper;
    }

    /**
     * Validate Checkout
     *
     * @param Quote $quote
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function validate(Quote $quote)
    {
        $this->validateAddress($quote);
        $this->validateShippingMethod($quote);
    }

    /**
     * Validate Addresses
     *
     * @param Quote $quote
     * @throws LocalizedException
     */
    protected function validateAddress(Quote $quote)
    {
        $billingAddress = $quote->getBillingAddress();
        $shippingAddress = $quote->getShippingAddress();

        $shippingAddressMissingFields = "";
        foreach ($this->requiredFields as $field) {
            if (!$shippingAddress->getData($field)) {
                $shippingAddressMissingFields .= $field . ",";
            }
        }
        if ($shippingAddressMissingFields) {
            $this->tandymHelper->logTandymActions(sprintf('Invalid Shipping Address : %s', $shippingAddressMissingFields));
            throw new LocalizedException(__(sprintf("Please check the shipping address on this input fields : %s", rtrim($shippingAddressMissingFields, ","))));
        }

        $billingAddressMissingFields = "";
        foreach ($this->requiredFields as $field) {
            if (!$billingAddress->getData($field)) {
                $billingAddressMissingFields .= $field . ",";
            }
        }
        if ($billingAddressMissingFields) {
            $this->tandymHelper->logTandymActions(sprintf('Invalid Billing Address : %s', $billingAddressMissingFields));
            throw new LocalizedException(__(sprintf("Please check the billing address on this input fields : %s", rtrim($billingAddressMissingFields, ","))));
        }
        $this->tandymHelper->logTandymActions("Address Validated!");
    }

    /**
     * Validate Shipping Method
     *
     * @param Quote $quote
     * @throws LocalizedException
     */
    protected function validateShippingMethod(Quote $quote)
    {
        if (!$quote->getShippingAddress()->getShippingMethod()) {
            $this->tandymHelper->logTandymActions('Please select a shipping method');
            throw new LocalizedException(__('Please select a shipping method'));
        }
    }
}
