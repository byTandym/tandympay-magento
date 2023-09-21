<?php
/*
 * @category    Tandym
 * @package     Tandym_Tandympay
 * @copyright   Copyright (c) Tandym (https://www.bytandym.com/)
 */


namespace Tandym\Tandympay\Model\System\Config\Source\Payment;

use Magento\Framework\Option\ArrayInterface;
use Tandym\Tandympay\Model\Tandym;

/**
 * Tandym Payment Action Dropdown source
 */
class OrderStatus implements ArrayInterface
{
    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => Tandym::STATE_NEW,
                'label' => __('Pending'),
            ],
            [
                'value' => Tandym::STATE_PROCESSING,
                'label' => __('Processing')
            ]
        ];
    }
}
