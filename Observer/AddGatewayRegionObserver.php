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
use Magento\Framework\Exception\InputException;
use Tandym\Tandympay\Model\System\Config\Config;
use Tandym\Tandympay\Model\System\Config\Container\TandymConfigInterface;
use Tandym\Tandympay\Model\System\Config\Container\TandymIdentity;

/**
 * Class AddGatewayRegionObserver
 * @package Tandym\Tandympay\Observer
 */
class AddGatewayRegionObserver implements ObserverInterface
{
    /**
     * @var TandymConfigInterface
     */
    private $tandymConfig;

    /**
     * AddGatewayRegionObserver constructor.
     * @param TandymConfigInterface $tandymConfig
     */
    public function __construct(
        TandymConfigInterface $tandymConfig
    ) {
        $this->tandymConfig = $tandymConfig;
    }

    /**
     * @param Observer $observer
     * @return AddGatewayRegionObserver
     * @throws InputException
     */
    public function execute(Observer $observer)
    {
        $website = $observer->getEvent()->getData('website');
        $store = $observer->getEvent()->getData('store');
        $changedPaths = $observer->getEvent()->getData('changed_paths');

        $haystack = [
            TandymIdentity::XML_PATH_PUBLIC_KEY,
            TandymIdentity::XML_PATH_PRIVATE_KEY,
            TandymIdentity::XML_PATH_PAYMENT_MODE
        ];

        if (count(array_intersect($haystack, $changedPaths)) <= 0) {
            return $this;
        }

        try {
            $this->tandymConfig->setGatewayRegion($website, $store);
        } catch (Exception $e) {
            throw new InputException(__('Tandym API Keys not validated'));
        }

        return $this;
    }
}
