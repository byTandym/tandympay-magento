<?php
/*
 * @category    Tandym
 * @package     Tandym_Tandympay
 * @copyright   Copyright (c) Tandym (https://www.bytandym.com/)
 */

namespace Tandym\Tandympay\Model;

use Exception;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Framework\Module\Manager;
use Tandym\Tandympay\Helper\Data;
use Tandym\Tandympay\Model\System\Config\Container\TandymConfigInterface;

/**
 * Class TandymConfigProvider
 * @package Tandym\Tandympay\Model
 */
class TandymConfigProvider implements ConfigProviderInterface
{

    /**
     * @var TandymConfigInterface
     */
    private $tandymConfig;
    /**
     * @var Data
     */
    private $tandymHelper;

    /**
     * @var Session
     */
    private $checkoutSession;
    /**
     * @var Tokenize
     */
    private $tokenizeModel;
    /**
     * @var Manager
     */
    private $moduleManager;
    /**
     * @var CurrencyInterface
     */
    private $localeCurrency;

    /**
     * TandymConfigProvider constructor.
     * @param TandymConfigInterface $tandymConfig
     * @param Data $tandymHelper
     * @param Session $checkoutSession
     * @param Manager $moduleManager
     * @param CurrencyInterface $localeCurrency
     */
    public function __construct(
        TandymConfigInterface $tandymConfig,
        Data $tandymHelper,
        Session $checkoutSession,
        Manager $moduleManager,
        CurrencyInterface $localeCurrency
    ) {
        $this->tandymHelper = $tandymHelper;
        $this->tandymConfig = $tandymConfig;
        $this->checkoutSession = $checkoutSession;
        $this->moduleManager = $moduleManager;
        $this->localeCurrency = $localeCurrency;
    }

    /**
     * @return array
     * @throws NoSuchEntityException|LocalizedException
     * @throws Exception
     */
    public function getConfig()
    {
        $quote = $this->checkoutSession->getQuote();

        return [
            'payment' => [
                Tandym::PAYMENT_CODE => [
                    'methodCode' => Tandym::PAYMENT_CODE,
                    'programName' => $this->tandymConfig->getProgramName(),
                    'programDescription' => $this->tandymConfig->getprogramDescription(),
                    'publicKey' => $this->tandymConfig->getPublicKey(),
                    'paymentTransactionMode' => $this->tandymConfig->getPaymentMode(),
                    'currencySymbol' => $this->localeCurrency->getCurrency($quote->getBaseCurrencyCode())->getSymbol(),
                    'gatewayRegion' => $this->tandymConfig->getGatewayRegion(),
                    'logo' => $this->tandymConfig->getProgramLogo(),
                ]
            ]
        ];
    }
}
