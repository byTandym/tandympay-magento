<?php

namespace Tandym\Tandympay\Controller\Payment;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\QuoteIdMask;
use Tandym\Tandympay\Controller\AbstractController\Tandymv2;    

/**
 * Class Complete
 * @package Tandym\Tandympay\Controller\Payment
 */

class Complete extends Tandymv2
{
    const TANDYM_MIDDLEWARE_REWARDS_ENDPOINT = "https://magento.api.platform.poweredbytandym.com/order/rewards";

    public function execute()
    {
        $redirect = 'checkout/cart';
        try {
            $quote = $this->checkoutSession->getQuote();
            $this->tandymHelper->logTandymActions("Returned from Tandym.");

            $referenceID = $this->getRequest()->getParam('transaction_receipt');
            $tandymOrderID = $this->getRequest()->getParam('order_id');
            $tandymOrderAmount = $this->getRequest()->getParam('order_val') != null ? $this->getRequest()->getParam('order_val') : 100;
            $cartManager = $this->customerSession->isLoggedIn() ? self::CART_MANAGER : self::GUEST_CART_MANAGER;
            $quoteId = $quote->getId();

            if($quoteId != null) {

                if ($cartManager === self::GUEST_CART_MANAGER) {
                    $quoteId = $this->quoteIdToMaskedQuoteIdInterface->execute($quoteId);
                }
                
                //Collect the Rewards Applied for this order
                $requestToSend = [];
                
                $requestToSend = [
                    'order_id' => $tandymOrderID,
                    'transaction_receipt' => $referenceID
                ];
                
                $url = self::TANDYM_MIDDLEWARE_REWARDS_ENDPOINT;
                $this->curl->addHeader("Content-Type", "application/json");
                $this->curl->post($url, json_encode($requestToSend));
                $this->tandymHelper->logTandymActions("Request sent to TANDYM Middleware for Rewards Applied");
                $this->tandymHelper->logTandymActions($url);
                $this->tandymHelper->logTandymActions($requestToSend);
                $responsefromtdm = $this->curl->getBody();
                $statusCode = $this->curl->getStatus();

                $this->tandymHelper->logTandymActions("Response from Tamdym Middleware with Response Status Code: $statusCode");
                $this->tandymHelper->logTandymActions($responsefromtdm);
                $_SESSION["tandym_rewards"] = 0;
                $tandymRewardsApplied = 0;

                if ($statusCode == "200") {
                    $body = $this->jsonHelper->jsonDecode($responsefromtdm);
                    
                    $tandymRewardsApplied =  isset($body['rewardsApplied']) && $body['rewardsApplied'] ? $body['rewardsApplied'] : 0;

                    $_SESSION["tandym_rewards"] = -1 * $tandymRewardsApplied;
                }

                //End of Rewards Applied Data Collection

                $payment = $quote->getPayment();
                $additionalInformation['tandym_rewards_applied'] = $_SESSION["tandym_rewards"];
                $additionalInformation['tandym_order_type'] = 'v2';
                $additionalInformation['tandym_reference_id'] = $referenceID;
                $additionalInformation['tandym_original_order_uuid'] = $tandymOrderID;
                $additionalInformation['tandym_checkout_type'] = "STANDARD";
                $additionalInformation['tandym_status'] = "APPROVED";

                
                $payment->setAdditionalInformation($additionalInformation);
                
                $quote->setPayment($payment);
                $this->tandymHelper->logTandymActions("Initiated Order Creation from Tandym.");
                
                $orderId = $this->$cartManager->placeOrder($quoteId);
                if (!$orderId) {
                    throw new CouldNotSaveException(__("Unable to place the order."));
                    $_SESSION["tandym_rewards"] = 0;
                }
               
                $this->tandymHelper->logTandymActions("Order Creation Success from Tandym.");
                
                $redirect = 'checkout/onepage/success';
            } else {
                $this->tandymHelper->logTandymActions("Order Creation Failed to Session Timeout");
                $transaction_error = 1;
                $transaction_error_message = "Invalid Request/Session";
                throw new LocalizedException(__('Session has timed out. Please re-login to complete the order.'));
                
            }
        } catch (CouldNotSaveException $e) {
            $this->tandymHelper->logTandymActions("11. Order Creation Failure - CouldNotSaveException");
            $transaction_error = 11;
            $transaction_error_message = "11. Invalid Request/Session";
            $failRespose = $this->handleOrderFailure($tandymOrderID,$referenceID,$tandymOrderAmount, "CouldNotSaveException" );
            $this->handleException($e);
        } catch (NoSuchEntityException $e) {
            $this->tandymHelper->logTandymActions("12. Order Creation Failure - NoSuchEntityException");
            $transaction_error = 12;
            $transaction_error_message = "12. Invalid Request/Session";
            $failRespose = $this->handleOrderFailure($tandymOrderID,$referenceID,$tandymOrderAmount, "NoSuchEntityException" );
            $this->handleException($e);
        } catch (LocalizedException $e) {
            $this->tandymHelper->logTandymActions("13. Order Creation Failure - LocalizedException");
            $transaction_error = 13;
            $transaction_error_message = "13. Invalid Request/Session";
            $failRespose = $this->handleOrderFailure($tandymOrderID,$referenceID,$tandymOrderAmount, "LocalizedException" );
            $this->handleException($e);
        } catch (Exception $e) {
            $this->tandymHelper->logTandymActions("14. Order Creation Failure - GenericException");
            $transaction_error = 13;
            $transaction_error_message = "13. Invalid Request/Session";
            $failRespose = $this->handleOrderFailure($tandymOrderID,$referenceID,$tandymOrderAmount, "GenericException" );
            $this->handleException($e);
        }

        return $this->_redirect($redirect);
    }

    /**
     * Handling Exception
     *
     * @param mixed $exc
     */
    private function handleException($exc)
    {
        $this->tandymHelper->logTandymActions("Tandym Transaction Exception: " . $exc->getMessage());
        $this->messageManager->addErrorMessage(
            $exc->getMessage()
        );
    }

    private function handleOrderFailure($tandymOrderUUID, $tandymReceipt, $amount, $reason)
    {
        $_SESSION["tandym_rewards"] = 0;
        $refundTxnUUID = "";
        $refundTxnUUID = $this->v2->refundonerror(
            $tandymReceipt,
            $tandymOrderUUID,
            $amount,
            $reason
        );

        return $refundTxnUUID;
    }

}
