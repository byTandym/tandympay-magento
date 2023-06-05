<?php

namespace Tandym\Tandympay\Controller\Payment;

use Magento\Framework\Exception\LocalizedException;
use Tandym\Tandympay\Controller\AbstractController\Tandym;    

/**
 * Class Cancel
 * @package Tandym\Tandympay\Controller\Payment
 */
class Cancel extends Tandym
{
    /**
     * Restore the quote if any
     * @throws LocalizedException
     */
    public function execute()
    {
        $order = $this->getOrder();
        $order->registerCancellation("Returned from Tandym Checkout without completing payment.");
        $this->tandymHelper->logTandymActions(
            "Returned from Tandym Checkout without completing payment. Order not created."
        );
        $this->getResponse()->setRedirect(
            $this->_url->getUrl('checkout')
        );
    }
}