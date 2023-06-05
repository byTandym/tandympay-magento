<?php
/*
 * @category    Tandym
 * @package     Tandym_Tandympay
 * @copyright   Copyright (c) Tandym (https://www.bytandym.com/)
 */

namespace Tandym\Tandympay\Api\Data;

/**
 * Interface LinkInterface
 * @package Tandym\Tandympay\Api\Data
 */
interface LinkInterface
{
    const HREF = 'href';
    const REL = "rel";
    const METHOD = "method";

    /**
     * @return string
     */
    public function getHref();

    /**
     * @param string $href
     * @return $this
     */
    public function setHref($href);

    /**
     * @return string
     */
    public function getRel();

    /**
     * @param string $rel
     * @return $this
     */
    public function setRel($rel);

    /**
     * @return string
     */
    public function getMethod();

    /**
     * @param string $method
     * @return $this
     */
    public function setMethod($method);
}
