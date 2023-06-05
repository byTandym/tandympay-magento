<?php
/*
 * @category    Tandym
 * @package     Tandym_Tandympay
 * @copyright   Copyright (c) Tandym (https://www.bytandym.com/)
 */

namespace Tandym\Tandympay\Model\System\Config\Source\Payment;

use Magento\Framework\Option\ArrayInterface;
use Tandym\Tandympay\Model\System\Config\Container\TandymIdentity;

/**
 * Class Mode
 * @package Tandym\Tandympay\Model\System\Config\Source\Payment
 */
class Mode implements ArrayInterface
{

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => TandymIdentity::PROD_MODE,
                'label' => 'Live',
            ],
            [
                'value' => TandymIdentity::SANDBOX_MODE,
                'label' => 'Sandbox',
            ]
        ];
    }
}
