<?php
/*
 * @category    Tandym
 * @package     Tandym_Tandympay
 * @copyright   Copyright (c) Tandym (https://www.bytandym.com/)
 */

namespace Tandym\Tandympay\Model\Api\Data;


use Magento\Framework\Api\AbstractExtensibleObject;
use Tandym\Tandympay\Api\Data\SessionTokenizeInterface;
use Tandym\Tandympay\Api\Data\TokenizeCustomerInterface;

class SessionTokenize extends AbstractExtensibleObject implements SessionTokenizeInterface
{

    /**
     * @inheritDoc
     */
    public function getStatus()
    {
        return $this->_get(self::STATUS);
    }

    /**
     * @inheritDoc
     */
    public function setStatus($status)
    {
        $this->setData(self::STATUS, $status);
    }

    /**
     * @inheritDoc
     */
    public function getToken()
    {
        return $this->_get(self::TOKEN);
    }

    /**
     * @inheritDoc
     */
    public function setToken($token)
    {
        $this->setData(self::TOKEN, $token);
    }

    /**
     * @inheritDoc
     */
    public function getApprovalUrl()
    {
        return $this->_get(self::APPROVAL_URL);
    }

    /**
     * @inheritDoc
     */
    public function setApprovalUrl($approvalURL)
    {
        $this->setData(self::APPROVAL_URL, $approvalURL);
    }

    /**
     * @inheritDoc
     */
    public function getExpiration()
    {
        return $this->_get(self::EXPIRATION);
    }

    /**
     * @inheritDoc
     */
    public function setExpiration($expiration)
    {
        $this->setData(self::EXPIRATION, $expiration);
    }

    /**
     * @inheritDoc
     */
    public function getCustomer()
    {
        return $this->_get(self::CUSTOMER);
    }

    /**
     * @inheritDoc
     */
    public function setCustomer(TokenizeCustomerInterface $customer = null)
    {
        $this->setData(self::CUSTOMER, $customer);
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
