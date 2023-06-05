<?php
/*
 * @category    Tandym
 * @package     Tandym_Tandympay
 * @copyright   Copyright (c) Tandym (https://www.bytandym.com/)
 */

namespace Tandym\Tandympay\Model\Api;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Http\ZendClient;

/**
 * Interface ProcessorInterface
 * @package Tandym\Tandympay\Model\Api
 */
interface ProcessorInterface
{
    /**
     * Call to Tandym Gateway
     *
     * @param string $url
     * @param string $authToken
     * @param bool|array $body
     * @param string $method
     * @param bool $getResponseStatusCode
     * @return array|string
     * @throws LocalizedException
     */
    public function call(
        $url,
        $authToken = null,
        $authSecret = null,
        $body = false,
        $method = ZendClient::GET,
        $getResponseStatusCode = false
    );
}
