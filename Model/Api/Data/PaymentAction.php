<?php
/*
 * @category    Tandym
 * @package     Tandym_Tandympay
 * @copyright   Copyright (c) Tandym (https://www.bytandym.com/)
 */

namespace Tandym\Tandympay\Model\Api\Data;


use Magento\Framework\Api\AbstractExtensibleObject;
use Tandym\Tandympay\Api\Data\AmountInterface;
use Tandym\Tandympay\Api\Data\PaymentActionInterface;

class PaymentAction extends AbstractExtensibleObject implements PaymentActionInterface
{

    /**
     * @inheritDoc
     */
    public function getUuid()
    {
        $this->_get(self::UUID);
    }

    /**
     * @inheritDoc
     */
    public function setUuid($uuid)
    {
        $this->setData(self::UUID, $uuid);
    }

    /**
     * @inheritDoc
     */
    public function getAmount()
    {
        return $this->_get(self::AMOUNT);
    }

    /**
     * @inheritDoc
     */
    public function setAmount(AmountInterface $amount = null)
    {
        $this->setData(self::AMOUNT, $amount);
    }
}
