<?php
/*
 * @category    Tandym
 * @package     Tandym_Tandympay
 * @copyright   Copyright (c) Tandym (https://www.bytandym.com/)
 */

namespace Tandym\Tandympay\Api\Data;

/**
 * Interface SessionOrderInterface
 * @package Tandym\Tandympay\Api\Data
 */
interface SessionOrderInterface
{
    const UUID = "uuid";
    const CHECKOUT_URL = "checkout_url";
    const LINKS = "links";

    /**
     * @return string|null
     */
    public function getUuid();

    /**
     * @param string $uuid
     * @return $this
     */
    public function setUuid($uuid);

    /**
     * @return string|null
     */
    public function getCheckoutUrl();

    /**
     * @param string $checkoutURL
     * @return $this
     */
    public function setCheckoutUrl($checkoutURL);

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
