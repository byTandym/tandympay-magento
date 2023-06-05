<?php
/*
 * @category    Tandym
 * @package     Tandym_Tandympay
 * @copyright   Copyright (c) Tandym (https://www.bytandym.com/)
 */

namespace Tandym\Tandympay\Api\Data;

/**
 * Interface SessionInterface
 * @package Tandym\Tandympay\Api\Data
 */
interface SessionInterface
{
    const UUID = "uuid";
    const ORDER = "order";
    const TOKENIZE = "tokenize";

    /**
     * @return string|null
     */
    public function getUuid();

    /**
     * @param $uuid
     * @return $this
     */
    public function setUuid($uuid);

    /**
     * @return \Tandym\Tandympay\Api\Data\SessionOrderInterface|null
     */
    public function getOrder();

    /**
     * @param \Tandym\Tandympay\Api\Data\SessionOrderInterface $sessionOrder
     * @return $this
     */
    public function setOrder(SessionOrderInterface $sessionOrder = null);

    /**
     * @return \Tandym\Tandympay\Api\Data\SessionTokenizeInterface|null
     */
    public function getTokenize();

    /**
     * @param \Tandym\Tandympay\Api\Data\SessionTokenizeInterface $sessionTokenize
     * @return mixed
     */
    public function setTokenize(SessionTokenizeInterface $sessionTokenize = null);
}
