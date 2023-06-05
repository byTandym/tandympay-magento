<?php
/*
 * @category    Tandym
 * @package     Tandym_Tandympay
 * @copyright   Copyright (c) Tandym (https://www.bytandym.com/)
 */

namespace Tandym\Tandympay\Api\Data;


/**
 * Interface AmountInterface
 * @package Tandym\Tandympay\Api\Data
 */
interface AmountInterface
{
    const AMOUNT_IN_CENTS = "amount_in_cents";
    const CURRENCY = "currency";

    /**
     * @return int|null
     */
    public function getAmountInCents();

    /**
     * @param int $amountInCents
     * @return $this
     */
    public function setAmountInCents($amountInCents);

    /**
     * @return string|null
     */
    public function getCurrency();

    /**
     * @param string $currency
     * @return $this
     */
    public function setCurrency($currency);

}
