<?php
/*
 * @category    Tandym
 * @package     Tandym_Tandympay
 * @copyright   Copyright (c) Tandym (https://www.bytandym.com/)
 */

namespace Tandym\Tandympay\Helper;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Tandym\Tandympay\Logger\Logger;
use Tandym\Tandympay\Model\System\Config\Container\TandymIdentity;
use Zend_Http_UserAgent_Mobile;

/**
 * Tandym Helper
 */
class Data extends AbstractHelper
{
    const PRECISION = 2;
    const TANDYM_LOG_FILE_PATH = '/var/log/tandympay.log';
    const TANDYM_MANUAL_INSTALL_COMPOSER_FILE_PATH = '/app/code/Tandym/Tandympay/composer.json';
    const TANDYM_COMPOSER_INSTALL_COMPOSER_FILE_PATH = '/vendor/Tandym/Tandympay/composer.json';

    /**
     * @var File
     */
    private $file;
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $jsonHelper;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var Logger
     */
    private $logger;
    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * Initialize dependencies.
     *
     * @param Context $context
     * @param File $file
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param StoreManagerInterface $storeManager
     * @param Logger $logger
     * @param CustomerSession $customerSession
     */
    public function __construct(
        Context $context,
        File $file,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        StoreManagerInterface $storeManager,
        Logger $logger,
        CustomerSession $customerSession
    ) {
        $this->file = $file;
        $this->jsonHelper = $jsonHelper;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->customerSession = $customerSession;
        parent::__construct($context);
    }

    /**
     * Dump Tandym log actions
     *
     * @param string|array|null $data
     * @return void
     */
    public function logTandymActions($data = null)
    {
        try {
            $logTrackerEnabled = $this->scopeConfig->getValue(
                TandymIdentity::XML_PATH_LOG_TRACKER,
                ScopeInterface::SCOPE_STORE,
                $this->storeManager->getStore()->getId()
            );
            if (!$logTrackerEnabled) {
                return;
            }

            if (is_array($data)) {
                $data = $this->jsonHelper->jsonEncode($data);
            }

            $customerSessionId = $this->customerSession->getSessionId();
            $logData = $customerSessionId . " " . $data;
            $this->logger->info($logData);
        } catch (NoSuchEntityException $e) {
        }
    }

    /**
     * Check if Device is Mobile or Tablet
     *
     * @return bool
     */
    public function isMobileOrTablet()
    {
        $userAgent = $this->_httpHeader->getHttpUserAgent();
        return Zend_Http_UserAgent_Mobile::match($userAgent, $_SERVER);
    }

    /**
     * Export CSV string to array
     *
     * @param string $content
     * @return array
     */
    public function csvToArray($content)
    {
        $data = ['header' => [], 'data' => []];
        $summary = [];
        $result = [];

        $lines = str_getcsv($content, "\n");
        foreach ($lines as $index => $line) {
            if ($index == 0 || $index == 2) {
                if ($index == 2) {
                    $summary = $data;
                    unset($data);
                }
                $data['header'] = str_getcsv($line);
            } else {
                $row = array_combine($data['header'], str_getcsv($line));
                $data['data'][] = $row;
            }
        }
        array_push($result, $summary, $data);
        return $result;
    }

    /**
     * Convert string from snake case to title case
     * @param string $name
     * @return string
     */
    public function snakeCaseToTitleCase($name)
    {
        $name = str_replace("_", " ", $name);
        $name = ucwords($name);
        return $name;
    }

    /**
     * Get Tandym Module Version
     */
    public function getVersion()
    {
        try {
            if ($this->file->isExists(BP . self::TANDYM_MANUAL_INSTALL_COMPOSER_FILE_PATH)) {
                $composerFilePath = BP . self::TANDYM_MANUAL_INSTALL_COMPOSER_FILE_PATH;
            } else {
                $composerFilePath = BP . self::TANDYM_COMPOSER_INSTALL_COMPOSER_FILE_PATH;
            }
            $file = $this->file->fileGetContents($composerFilePath);
            if ($file) {
                $contents = $this->jsonHelper->jsonDecode($file);
                if (is_array($contents) && isset($contents['version'])) {
                    return $contents['version'];
                }
            }
        } catch (FileSystemException $e) {
            $this->logTandymActions("Module not found");
            return '--';
        }
        return '--';
    }

    /**
     * Get amount in cents
     *
     * @param float $amount
     * @return int
     */
    public function getAmountInCents($amount)
    {
        return (int)(round(
            ($amount ?? 0) * 100,
            self::PRECISION
        ));
    }
}
