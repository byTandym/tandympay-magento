<?php

namespace Tandym\Tandympay\Model\Creditmemo\Total;

use Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal;

class TandymRewards extends AbstractTotal
{
    /**
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @return $this
     */
    public function collect(\Magento\Sales\Model\Order\Creditmemo $creditmemo)
    {
        $creditmemo->setTandymRewards(0);
        
        $amount = $creditmemo->getOrder()->getTandymRewards();
        $creditmemo->setTandymRewards($amount);

        $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $creditmemo->getTandymRewards());
        $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $creditmemo->getTandymRewards());

        return $this;
    }
}
