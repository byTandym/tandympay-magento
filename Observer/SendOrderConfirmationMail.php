<?php
/*
 * @category    Tandym
 * @package     Tandym_Tandympay
 * @copyright   Copyright (c) Tandym (https://www.bytandym.com/)
 */

namespace Tandym\Tandympay\Observer;

use Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Tandym\Tandympay\Helper\Data;
use Tandym\Tandympay\Model\Tandym;

class SendOrderConfirmationMail implements ObserverInterface
{

    /**
     * @var OrderSender
     */
    private $orderSender;
    /**
     * @var Data
     */
    private $tandymHelper;

    /**
     * SendOrderConfirmationMail constructor.
     * @param OrderSender $orderSender
     * @param Data $tandymHelper
     */
    public function __construct(
        OrderSender $orderSender,
        Data $tandymHelper
    ) {
        $this->orderSender = $orderSender;
        $this->tandymHelper = $tandymHelper;
    }

    /**
     * @param Observer $observer
     * @return SendOrderConfirmationMail
     */
    public function execute(Observer $observer)
    {
        /* @var Order $order */
        $order = $observer->getEvent()->getData('order');
        try {
            if (!$order || $order->getPayment()->getMethod() !== Tandym::PAYMENT_CODE) {
                return $this;
            }
            $this->orderSender->send($order);
        } catch (Exception $e) {
            $this->tandymHelper->logtandymActions(
                "Tandym Order Confirmation Mail Sending Error: " .
                $e->getMessage()
            );
        }

        return $this;
    }
}
