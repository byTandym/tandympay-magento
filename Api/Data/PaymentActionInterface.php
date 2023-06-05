<?php
/*
 * @category    Tandym
 * @package     Tandym_Tandympay
 * @copyright   Copyright (c) Tandym (https://www.bytandym.com/)
 */

namespace Tandym\Tandympay\Api\Data;


/**
 * Interface PaymentActionInterface
 * @package Tandym\Tandympay\Api\Data
 */
interface PaymentActionInterface
{
    const UUID = "uuid";
    const AMOUNT = "amount";

    /**
     * @return string|null
     */
    public function getUuid();

    /**
     * @param string $uuid
     * @return $this
     */
    public function setUuid($uuid);

    /**
     * @return \Tandym\Tandympay\Api\Data\AmountInterface|null
     */
    public function getAmount();

    /**
     * @param \Tandym\Tandympay\Api\Data\AmountInterface $amount
     * @return $this
     */
    public function setAmount(AmountInterface $amount = null);

}
