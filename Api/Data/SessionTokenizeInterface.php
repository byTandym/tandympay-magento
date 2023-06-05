<?php
/*
 * @category    Tandym
 * @package     Tandym_Tandympay
 * @copyright   Copyright (c) Tandym (https://www.bytandym.com/)
 */

namespace Tandym\Tandympay\Api\Data;


/**
 * Interface SessionTokenizeInterface
 * @package Tandym\Tandympay\Api\Data
 */
interface SessionTokenizeInterface
{
    const TOKEN = "token";
    const STATUS = "status";
    const APPROVAL_URL = "approval_url";
    const EXPIRATION = "expiration";
    const CUSTOMER = "customer";
    const LINKS = "links";

    /**
     * @return string|null
     */
    public function getStatus();

    /**
     * @param string $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * @return string|null
     */
    public function getToken();

    /**
     * @param string $token
     * @return $this
     */
    public function setToken($token);

    /**
     * @return string|null
     */
    public function getApprovalUrl();

    /**
     * @param string $approvalURL
     * @return $this
     */
    public function setApprovalUrl($approvalURL);

    /**
     * @return string|null
     */
    public function getExpiration();

    /**
     * @param string $expiration
     * @return $this
     */
    public function setExpiration($expiration);

    /**
     * @return \Tandym\Tandympay\Api\Data\TokenizeCustomerInterface|null
     */
    public function getCustomer();

    /**
     * @param \Tandym\Tandympay\Api\Data\TokenizeCustomerInterface $customer
     * @return $this
     */
    public function setCustomer(TokenizeCustomerInterface $customer = null);

    /**
     * @return \Tandym\Tandympay\Api\Data\LinkInterface[]|null
     */
    public function getLinks();

    /**
     * @param \Tandym\Tandympay\Api\Data\LinkInterface[] $links
     * @return $this
     */
    public function setLinks(array $links = null);

}
