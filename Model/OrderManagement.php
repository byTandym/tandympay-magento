<?php
/*
 * @category    Tandym
 * @package     Tandym_Tandympay
 * @copyright   Copyright (c) Tandym (https://www.bytandym.com/)
 */

namespace Tandym\Tandympay\Model;

use Magento\Checkout\Api\PaymentInformationManagementInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Tandym\Tandympay\Api\OrderManagementInterface;
use Tandym\Tandympay\Model\Order\SaveHandler;

/**
 * Class OrderManagement
 * @package Tandym\Tandympay\Model
 */
class OrderManagement implements OrderManagementInterface
{

    /**
     * @var CartRepositoryInterface
     */
    protected $cartRepository;
    /**
     * @var
     */
    private $saveHandler;
    /**
     * @var PaymentInformationManagementInterface
     */
    private $paymentInformationManagement;

    /**
     * Payment constructor.
     * @param CartRepositoryInterface $cartRepository
     * @param PaymentInformationManagementInterface $paymentInformationManagement
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        PaymentInformationManagementInterface $paymentInformationManagement
    ) {
        $this->cartRepository = $cartRepository;
        $this->paymentInformationManagement = $paymentInformationManagement;
    }

    /**
     * @inheritDoc
     */
    public function createCheckout(
        $cartId,
        PaymentInterface $paymentMethod,
        AddressInterface $billingAddress = null
    ) {
        try {
            if (!$this->paymentInformationManagement->savePaymentInformation(
                $cartId,
                $paymentMethod,
                $billingAddress
            )) {
                throw new NotFoundException(__("Unable to save payment information."));
            }

            return $this->getSaveHandler()->createCheckout();
        } catch (NoSuchEntityException $e) {
            throw new CouldNotSaveException(
                __($e->getMessage()),
                $e
            );
        } catch (LocalizedException $e) {
            throw new CouldNotSaveException(
                __($e->getMessage()),
                $e
            );
        }
    }

    /**
     * Get Save Handler
     *
     * @return SaveHandler
     */
    private function getSaveHandler()
    {
        if (!$this->saveHandler) {
            $this->saveHandler = ObjectManager::getInstance()->get(SaveHandler::class);
        }
        return $this->saveHandler;
    }
}
