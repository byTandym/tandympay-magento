<?php
/*
 * @category    Tandym
 * @package     Tandym_Tandympay
 * @copyright   Copyright (c) Tandym (https://www.bytandym.com/)
 */

namespace Tandym\Tandympay\Model\Api;

use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfig;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\HTTP\ZendClient;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Psr\Log\LoggerInterface as Logger;
use Tandym\Tandympay\Helper\Data as TandymHelper;
use Tandym\Tandympay\Model\System\Config\Container\TandymConfigInterface;

/**
 * Class Processor
 * @package Tandym\Tandympay\Model\Api
 */
class Processor implements ProcessorInterface
{

    /**
     * @var JsonHelper
     */
    protected $jsonHelper;
    /**
     * @var Logger
     */
    protected $logger;
    /**
     * @var ScopeConfig
     */
    protected $scopeConfig;
    /**
     * @var Curl
     */
    protected $curl;

    /**
     * @var TandymHelper
     */
    protected $tandymHelper;

    /**
     * @var TandymConfigInterface
     */
    private $tandymConfig;

    /**
     * Processor constructor.
     * @param Curl $curl
     * @param TandymHelper $tandymHelper
     * @param JsonHelper $jsonHelper
     * @param Logger $logger
     * @param ScopeConfig $scopeConfig
     * @param TandymConfigInterface $tandymConfig
     */
    public function __construct(
        Curl $curl,
        TandymHelper $tandymHelper,
        JsonHelper $jsonHelper,
        Logger $logger,
        ScopeConfig $scopeConfig,
        TandymConfigInterface $tandymConfig
    ) {
        $this->curl = $curl;
        $this->tandymHelper = $tandymHelper;
        $this->jsonHelper = $jsonHelper;
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->tandymConfig = $tandymConfig;
    }

    /**
     * @inheritDoc
     */
    public function call($url, $authToken = null, $authSecret = null, $body = false, $method = ZendClient::GET, $getResponseStatusCode = false)
    {
        try {
            if ($authToken) {
               $this->curl->addHeader("apikey", $authToken);
            }
            if ($authSecret) {
                $this->curl->addHeader("secret", $authSecret);
             }
            if (strpos("reauthorize", $url) !== false) {
                $platformKey = base64_encode($this->jsonHelper->jsonEncode(["id" => "magento:" . $this->tandymConfig->getMerchantUUID()]));
                $this->curl->addHeader("Tandym-Platform", $platformKey);
            }
            $this->tandymHelper->logTandymActions("API Key token : $authToken");
            $this->tandymHelper->logTandymActions("****Request Info****");
            $requestLog = [
                'type' => 'Request',
                'method' => $method,
                'url' => $url,
                'body' => $body
            ];
            $this->tandymHelper->logTandymActions($requestLog);
            $this->curl->setTimeout(ApiParamsInterface::TIMEOUT);
            $this->curl->addHeader("Content-Type", ApiParamsInterface::CONTENT_TYPE_JSON);
            switch ($method) {
                case 'POST':
                    $this->curl->post($url, $this->jsonHelper->jsonEncode($body));
                    break;
                case 'GET':
                    $this->curl->get($url);
                    break;
                default:
                    break;
            }

            $response = $this->curl->getBody();

            $responseLog = [
                'type' => 'Response',
                'method' => $method,
                'url' => $url,
                'httpStatusCode' => $this->curl->getStatus()
            ];
            $this->tandymHelper->logTandymActions("****Response Info****");
            $this->tandymHelper->logTandymActions($responseLog);
        } catch (\Exception $e) {
            $this->tandymHelper->logTandymActions($e->getMessage());
            throw new LocalizedException(
                __('Gateway error: %1', $e->getMessage())
            );
        }
        if ($getResponseStatusCode) {
            return [
                "body" => $response,
                "status_code" => $this->curl->getStatus()
            ];
        }
        return $response;
    }
}
