<?php
/*
 * @category    Tandym
 * @package     Tandym_Tandympay
 * @copyright   Copyright (c) Tandym (https://www.bytandym.com/)
 */

namespace Tandym\Tandympay\Model;

use Magento\Checkout\Api\GuestPaymentInformationManagementInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Tandym\Tandympay\Api\GuestOrderManagementInterface;
use Tandym\Tandympay\Model\Order\SaveHandler;

class GuestOrderManagement implements GuestOrderManagementInterface
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
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;
    /**
     * @var GuestPaymentInformationManagementInterface
     */
    private $paymentInformationManagement;

    /**
     * Payment constructor.
     * @param CartRepositoryInterface $cartRepository
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param GuestPaymentInformationManagementInterface $paymentInformationManagement
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        GuestPaymentInformationManagementInterface $paymentInformationManagement
    ) {
        $this->cartRepository = $cartRepository;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->paymentInformationManagement = $paymentInformationManagement;
    }

    /**
     * @inheritDoc
     */
    public function createCheckout(
        $cartId,
        $email,
        PaymentInterface $paymentMethod,
        AddressInterface $billingAddress = null
    ) {
        try {
            if (!$this->paymentInformationManagement->savePaymentInformation(
                $cartId,
                $email,
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
        } catch (NotFoundException $e) {
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
