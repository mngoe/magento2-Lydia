<?php
namespace Ynote\Lydia\Controller\Payment;

use Ynote\Lydia\Controller\Index\Index;
use Ynote\Lydia\Model\Api\Request;

use Ynote\Lydia\Model\Api\Response;

class Cancel extends Index
{
    /**
     * Dispatch request
     * When a customer cancel payment from Lydia Standard.
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        if (!array_key_exists('message', $_REQUEST)) {
            // Set redirect message
            $this->getLydiaSession()->setRedirectMessage(('An error occured: no data received.'));
            // Log error
            $errorMessage = ('Customer #' . $this->getCustomerSession()->getCustomerId() . ' returned successfully from Lydia payment platform but no data received for order #' . $this->getCheckoutSession()->getLastRealOrder()->getId() . '');
            $this->lydiaHelper->logError(get_class($this), __FUNCTION__, $errorMessage);
            // Redirect
            $this->_redirect('*/*/failure');
            return;
        }

        $errorMessage = ('Customer #' . $this->getCustomerSession()->getCustomerId() . ' returned successfully from Lydia payment platform '.$_REQUEST["DATA"].' for order #' . $this->getCheckoutSession()->getLastRealOrder()->getId() . '');
        $this->lydiaHelper->logError(get_class($this), __FUNCTION__, $errorMessage);
        $this->_redirect('*/*/failure');
        return;

        // Get Sips Server Response
        $response = $this->_getLydiaResponse($_REQUEST['DATA']);

        // Debug
        $this->getMethodInstance()->debugResponse($response['hash'], 'Cancel');

        // Set redirect URL
        $response['redirect_url'] = '*/*/failure';

        // Set redirect message
        $this->getLydiaSession()->setRedirectTitle(('Your payment has been rejected'));
        $describedResponse = $this->getApiResponse()->describeResponse($response['hash'], 'array');
        $this->getLydiaSession()->setRedirectMessage(('The payment platform has rejected your transaction with the message: <strong>' . $describedResponse['response_code'] . '</strong>.'));

        // Cancel order
        if ($response['hash']['order_id']) {
            $order =  $this->orderInterface->loadByIncrementId($response['hash']['order_id']);
            if ($response['hash']['response_code'] == 17) {
                $message = $this->getApiResponse()->describeResponse($response['hash']);
            } else {
                $message = ('Automatic cancel');
                if (array_key_exists('bank_response_code', $describedResponse)) {
                    $this->getLydiaSession()->setRedirectMessage(__('The payment platform has rejected your transaction with the message: <strong>%1</strong>, because the bank send the error: <strong>%2</strong>.', $describedResponse['response_code'], $describedResponse['bank_response_code']));
                } else {
                    $this->getLydiaSession()->setRedirectMessage(__('The payment platform has rejected your transaction with the message: <strong>%1</strong>.', $describedResponse['response_code']));
                }
            }
            if ($order->getId()) {
                if ($order->canCancel()) {
                    try {
                        $order->registerCancellation($message)->save();
                    } catch (\Magento\Framework\Exception\LocalizedException $e) {
                        $this->logger->critical($e);
                    } catch (\Exception $e) {
                        $this->logger->critical($e);
                        $message .= '<br/><br/>';
                        $message .= ('The order has not been cancelled.') . ' : ' . $e->getMessage();
                        $order->addStatusHistoryComment($message)->save();
                    }
                } else {
                    $message .= '<br/><br/>';
                    $message .= ('The order was already cancelled.');
                    $order->addStatusHistoryComment($message)->save();
                }
            }
            // Refill cart
            //Mage::helper('atos')->reorder($response['hash']['order_id']);
        }

        // Save Lydia response in session
        $this->getLydiaSession()->setResponse($response);
        //$this->_redirect($response['redirect_url'], ['_secure' => true]);
        $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath($response['redirect_url']);
        return $resultRedirect;
    }
}
