<?php

namespace Tandym\Tandympay\Block\Sales\Totals;


class TandymRewards extends \Magento\Framework\View\Element\Template
{

    /**
     * @var Order
     */
    protected $_order;

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_source;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    )
    {
        parent::__construct($context, $data);
    }

    /**
     * Check if we nedd display full tax total info
     *
     * @return bool
     */
    public function displayFullSummary()
    {
        return true;
    }

    /**
     * Get data (totals) source model
     *
     * @return \Magento\Framework\DataObject
     */
    public function getSource()
    {
        return $this->_source;
    }

    public function getStore()
    {
        return $this->_order->getStore();
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->_order;
    }

    /**
     * @return array
     */
    public function getLabelProperties()
    {
        return $this->getParentBlock()->getLabelProperties();
    }

    /**
     * @return array
     */
    public function getValueProperties()
    {
        return $this->getParentBlock()->getValueProperties();
    }

    /**
     * @return $this
     */
    public function initTotals()
    {
        $parent = $this->getParentBlock();
        $this->_order = $parent->getOrder();
        $this->_source = $parent->getSource();
        //$store = $this->getStore();

        if(!$this->getSource()->getTandymRewards() || $this->getSource()->getTandymRewards() == 0) {
            return $this;
        }
       
        $fee = new \Magento\Framework\DataObject(
            [
                'code' => 'tandym_rewards',
                'strong' => false,
                'value' => $this->_source->getTandymRewards(),
                'label' => "Tandym Rewards",
            ]
        );

        $parent->addTotal($fee, 'tandym_rewards');

        return $this;
    }

}
