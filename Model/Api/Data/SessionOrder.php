<?php
/*
 * @category    Tandym
 * @package     Tandym_Tandympay
 * @copyright   Copyright (c) Tandym (https://www.bytandym.com/)
 */

namespace Tandym\Tandympay\Model\Api\Data;


use Magento\Framework\Api\AbstractExtensibleObject;
use Tandym\Tandympay\Api\Data\SessionOrderInterface;

class SessionOrder extends AbstractExtensibleObject implements SessionOrderInterface
{

    /**
     * @inheritDoc
     */
    public function getUuid()
    {
        return $this->_get(self::UUID);
    }

    /**
     * @inheritDoc
     */
    public function setUuid($uuid)
    {
        $this->setData(self::UUID, $uuid);
    }

    /**
     * @inheritDoc
     */
    public function getCheckoutUrl()
    {
        return $this->_get(self::CHECKOUT_URL);
    }

    /**
     * @inheritDoc
     */
    public function setCheckoutUrl($checkoutURL)
    {
        $this->setData(self::CHECKOUT_URL, $checkoutURL);
    }

    /**
     * @inheritDoc
     */
    public function getLinks()
    {
        return $this->_get(self::LINKS);
    }

    /**
     * @inheritDoc
     */
    public function setLinks(array $links = null)
    {
        $this->setData(self::LINKS, $links);
    }
}
