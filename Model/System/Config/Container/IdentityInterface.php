<?php
/*
 * @category    Tandym
 * @package     Tandym_Tandympay
 * @copyright   Copyright (c) Tandym (https://www.bytandym.com/)
 */

namespace Tandym\Tandympay\Model\System\Config\Container;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;

/**
 * Interface IdentityInterface
 * @package Tandym\Tandympay\Model\System\Config\Container
 */
interface IdentityInterface
{
    /**
     * Check if payment method is enabled
     * @return bool
     * @throws NoSuchEntityException
     */
    public function isEnabled();

    /**
     * Get store
     * @return Store
     */
    public function getStore();

    /**
     * Set Store
     * @param Store $store
     * @return mixed
     */
    public function setStore(Store $store);
}
