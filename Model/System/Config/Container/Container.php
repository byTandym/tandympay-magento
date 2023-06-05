<?php
/*
 * @category    Tandym
 * @package     Tandym_Tandympay
 * @copyright   Copyright (c) Tandym (https://www.bytandym.com/)
 */

namespace Tandym\Tandympay\Model\System\Config\Container;

use Exception;
use Magento\Config\Model\ResourceModel\Config as ResourceConfig;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\HTTP\Header;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\UrlInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Tandym\Tandympay\Helper\Data;
use Tandym\Tandympay\Model\Api\ApiParamsInterface;
use Tandym\Tandympay\Model\System\Config\Config;

/**
 * Class Container
 * @package Tandym\Tandympay\Model\System\Config\Container
 */
abstract class Container implements IdentityInterface
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Core store config
     *
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Store
     */
    protected $store;

    /**
     * @var string
     */
    protected $customerName;

    /**
     * @var string
     */
    protected $customerEmail;
    /**
     * @var UrlInterface
     */
    public $urlBuilder;
    /**
     * @var Header
     */
    protected $httpHeader;
    /**
     * @var Data
     */
    protected $tandymHelper;
    /**
     * @var ResourceConfig
     */
    protected $resourceConfig;
    /**
     * @var Curl
     */
    private $curl;
    /**
     * @var AuthInterfaceFactory
     */
    private $authFactory;
    /**
     * @var JsonHelper
     */
    private $jsonHelper;
    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @param UrlInterface $urlBuilder
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param Header $httpHeader
     * @param Data $tandymHelper
     * @param ResourceConfig $resourceConfig
     * @param Curl $curl
     * @param AuthInterfaceFactory $authFactory
     * @param JsonHelper $jsonHelper
     * @param DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        UrlInterface $urlBuilder,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        Header $httpHeader,
        Data $tandymHelper,
        ResourceConfig $resourceConfig,
        Curl $curl,
        JsonHelper $jsonHelper,
        DataObjectHelper $dataObjectHelper
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->httpHeader = $httpHeader;
        $this->tandymHelper = $tandymHelper;
        $this->resourceConfig = $resourceConfig;
        $this->curl = $curl;
        $this->jsonHelper = $jsonHelper;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    /**
     * Return store
     *
     * @return StoreInterface
     * @throws NoSuchEntityException
     */
    public function getStore()
    {
        //current store
        if ($this->store instanceof Store) {
            return $this->store;
        }
        return $this->storeManager->getStore();
    }

    /**
     * Set current store
     *
     * @param Store $store
     * @return void
     */
    public function setStore(Store $store)
    {
        $this->store = $store;
    }

    /**
     * Get config value
     *
     * @param string $path
     * @param string $storeId
     * @param null|int|string $scope
     * @return mixed
     */
    protected function getConfigValue($path, $storeId, $scope = ScopeInterface::SCOPE_STORE)
    {
        return $this->scopeConfig->getValue(
            $path,
            $scope,
            $storeId
        );
    }

    
}
