<?php
namespace Tandym\Tandympay\Observer;
use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfig;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

use Magento\Framework\HTTP\Client\Curl;
use Psr\Log\LoggerInterface as Logger;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Tandym\Tandympay\Helper\Data as TandymHelper;
use Tandym\Tandympay\Model\System\Config\Container\TandymConfigInterface;

class ConfigChange implements ObserverInterface
{
    const TANDYM_LIVE_PROGRAM_ENDPOINT = "https://api.bytandym.com/merchants/program/details";
    const TANDYM_STAGING_PROGRAM_ENDPOINT = "https://api.staging.poweredbytandym.com/merchants/program/details";
    const TANDYM_MIDDLEWARE_PROGRAM_ENDPOINT = "https://magento.api.platform.poweredbytandym.com/program-info";
    /**
     * @var JsonHelper
     */
    protected $jsonHelper;
    /**
     * @var Logger
     */
    protected $logger;
    /**
     * 
     * @var Curl
     */
    protected $curl;

    /**
     * @var TandymHelper
     */
    protected $tandymHelper;
    /**
     * @var ScopeConfig
     */
    protected $scopeConfig;
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

    public function execute(EventObserver $observer)
    {
        $this->tandymHelper->logTandymActions("Tamdym Config Changed");
        $tandym_public_key = $this->tandymConfig->getPublicKey();
        $tandym_secret_key = $this->tandymConfig->getPrivateKey();
        $tandym_payment_mode = $this->tandymConfig->getPaymentMode();
        $response = "";

        try {
            if ($tandym_public_key != "" && $tandym_secret_key != "") {
                if ($tandym_payment_mode == "sandbox") {
                    $url = self::TANDYM_STAGING_PROGRAM_ENDPOINT;
                } else {
                    $url = self::TANDYM_LIVE_PROGRAM_ENDPOINT;
                }
                $this->curl->addHeader("apikey", $tandym_public_key);
                $this->curl->addHeader("secret", $tandym_secret_key);
                $this->curl->addHeader("Content-Type", "application/json");
                $this->curl->get($url);
                $response = $this->curl->getBody();
                $response = json_decode($response);
    
                if ($tandym_payment_mode == "sandbox") {
                    $response->testMode = true;
                } else {
                    $response->testMode = false;
                }
                $response->ecomplatform = "Tandym Magento Program Info";
                $response = json_encode($response);
                $statusCode = $this->curl->getStatus();
                
                $this->tandymHelper->logTandymActions("Tamdym Merchant Info Response Status Code: $statusCode");
                $this->tandymHelper->logTandymActions($response);

                if ($statusCode == "200") {
                    $this->tandymHelper->logTandymActions("Updating TANDYM Middleware");
                    $url = self::TANDYM_MIDDLEWARE_PROGRAM_ENDPOINT;
                    $this->curl->addHeader("apikey", $tandym_public_key);
                    $this->curl->addHeader("secret", $tandym_secret_key);
                    $this->curl->addHeader("Content-Type", "application/json");
                    $this->curl->post($url, $response);

                    $responsefromtdm = $this->curl->getBody();
                    $statusCode = $this->curl->getStatus();

                    $this->tandymHelper->logTandymActions("Updated Tamdym Middleware Merchant Info Response Status Code: $statusCode");
                    $this->tandymHelper->logTandymActions($responsefromtdm);
                }
            }
        } catch (\Exception $e) {
            $this->tandymHelper->logTandymActions("Tamdym Config Response Exception");
            $this->tandymHelper->logTandymActions($e->getMessage());
            throw new LocalizedException(
                __('Gateway error: %1', $e->getMessage())
            );
        }
        return $this;
    }
}
?>