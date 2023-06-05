<?php
/*
 * @category    Tandym
 * @package     Tandym_Tandympay
 * @copyright   Copyright (c) Tandym (https://www.bytandym.com/)
 */

namespace Tandym\Tandympay\Block\Onepage;

use Tandym\Tandympay\Model\Tandym;

class Success extends \Magento\Checkout\Block\Onepage\Success
{

    /**
     * Check if the last real order is Tandym order
     * @return bool
     */
    public function isTandymOrder()
    {
        $order = $this->_checkoutSession->getLastRealOrder();
        if (!$order->getId()) {
            return false;
        }
        return $order->getPayment()->getMethod() === Tandym::PAYMENT_CODE;
    }
}
