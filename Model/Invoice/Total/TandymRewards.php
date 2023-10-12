<?php

namespace Tandym\Tandympay\Model\Invoice\Total;

use Magento\Sales\Model\Order\Invoice\Total\AbstractTotal;

class TandymRewards extends AbstractTotal
{
    /**
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @return $this
     */
    public function collect(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        $invoice->setTandymRewards(0);
        
        $amount = $invoice->getOrder()->getTandymRewards();
        $invoice->setTandymRewards($amount);
       
        $invoice->setGrandTotal($invoice->getGrandTotal() + $invoice->getTandymRewards());
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $invoice->getTandymRewards());

        return $this;
    }
}
