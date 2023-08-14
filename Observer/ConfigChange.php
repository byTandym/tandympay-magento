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
    const TANDYM_MIDDLEWARE_PROGRAM_REGISTER_ENDPOINT = "https://magento.api.platform.poweredbytandym.com/program-info/register";
    const TANDYM_STAGING_EXPRESS_PROGRAM_ENDPOINT = "https://stagingapi.platform.poweredbytandym.com/merchants/register";
    const TANDYM_LIVE_EXPRESS_PROGRAM_ENDPOINT = "https://plugin.api.platform.poweredbytandym.com/merchants/register";
    

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

        $requestToSend = [];
        if ($tandym_payment_mode == "sandbox") {
            $requestToSend = [
                'testMode' => true
            ];
        } else {
            $requestToSend = [
                'testMode' => false
            ];
        }

        try {
            if ($tandym_public_key != "" && $tandym_secret_key != "") {
                    
                $this->tandymHelper->logTandymActions("Registering with TANDYM Middleware");
                $url = self::TANDYM_MIDDLEWARE_PROGRAM_REGISTER_ENDPOINT;
                $this->curl->addHeader("apikey", $tandym_public_key);
                $this->curl->addHeader("secret", $tandym_secret_key);
                $this->curl->addHeader("Content-Type", "application/json");
                $this->curl->post($url, json_encode($requestToSend));
                $this->tandymHelper->logTandymActions("Request sent to TANDYM Middleware");
                $this->tandymHelper->logTandymActions($url);
                $this->tandymHelper->logTandymActions($requestToSend);
                $responsefromtdm = $this->curl->getBody();
                $statusCode = $this->curl->getStatus();

                $this->tandymHelper->logTandymActions("Response from Tamdym Middleware with Response Status Code: $statusCode");
                $this->tandymHelper->logTandymActions($responsefromtdm);
                
                if ($statusCode == "200") {
                    $this->tandymHelper->logTandymActions("Registering with TANDYM Express Middleware");
                    if ($tandym_payment_mode == "sandbox") {
                        $url = self::TANDYM_STAGING_EXPRESS_PROGRAM_ENDPOINT;
                    } else {
                        $url = self::TANDYM_LIVE_EXPRESS_PROGRAM_ENDPOINT;
                    }
                    $this->curl->addHeader("apikey", $tandym_public_key);
                    $this->curl->addHeader("secret", $tandym_secret_key);
                    $this->curl->addHeader("Content-Type", "application/json");
                    $this->curl->post($url, json_encode($requestToSend));
                    $this->tandymHelper->logTandymActions("Request sent to TANDYM Express Middleware");
                    $this->tandymHelper->logTandymActions($url);
                    $this->tandymHelper->logTandymActions($requestToSend);
                    $responsefromtdmexpress = $this->curl->getBody();
                    $statusCode = $this->curl->getStatus();

                    $this->tandymHelper->logTandymActions("Response from Tamdym Express Middleware with Response Status Code: $statusCode");
                    $this->tandymHelper->logTandymActions($responsefromtdmexpress);
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