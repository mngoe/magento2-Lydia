<?php
namespace Ynote\Lydia\Controller\Payment;

use Ynote\Lydia\Controller\Index\Index;
use Ynote\Lydia\Model\Api\Request;
use Ynote\Lydia\Model\Api\Response;
use Ynote\Lydia\Helper\Data;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Framework\DB\Transaction;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;

class NormalCallback extends Index
{


    /**
     * Dispatch request
     * When customer returns from Lydia payment platform
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        
        if (!(array_key_exists('uniqueID', $_REQUEST) || array_key_exists('transaction', $_REQUEST))) {
            // Set redirect message
            $this->getLydiaSession()->setRedirectMessage(('An error occured: no data received.'));
            // Log error
            $errorMessage = __('Customer #%1 returned successfully from Lydia payment platform but no data received for order #%2.', $this->getCustomerSession()->getCustomerId(), $this->getCheckoutSession()->getLastRealOrder()->getId());

            //echo "<pre> request: print_r($_REQUEST, 1)</pre>";
            $this->logger->critical($errorMessage);
            $this->lydiaHelper->logError(get_class($this), __FUNCTION__, $errorMessage);

            // Redirect
            $this->_redirect('*/*/failure');
            return;
        }



        // Get Lydia Server Response
        $response = [];
        $this->getConfig()->initMethod('lydia_standard');

        $stateParams = array('order_ref'=>$_REQUEST["uniqueID"], 
            'vendor_token'=>$this->_config->getConfigData('vendor_token', 'lydia_standard'));
        
        $response = $this->lydiaHelper->processGatewayCall($stateParams,'api/request/state.json');
       
        if($response ["data"]->state == 1){

            // Treat response
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $orders = $objectManager->create('Magento\Sales\Model\ResourceModel\Order\CollectionFactory')->create($customerId)
            ->addFieldToSelect('*')
            ->addFieldToFilter('lydia_payment_id',
                    ['eq' => $_REQUEST["uniqueID"]]
                );
            foreach($orders as $item){
                if($item->canInvoice()){
                    // Change Order Status 
                    $orderState = Order::STATE_PROCESSING;
                    $item->setState($orderState)->setStatus(Order::STATE_PROCESSING);
                    $item->save();
                    // Create Invoice 
                    $invoice = $objectManager->create('Magento\Sales\Model\Service\InvoiceService')->prepareInvoice($item);
                    $invoice->register();
                    $invoice->save();
                    $transactionSave = $objectManager->create('Magento\Framework\DB\Transaction')->addObject(
                        $invoice
                    )->addObject(
                        $invoice->getOrder()
                    );
                    $transactionSave->save();
                    $objectManager->create('Magento\Sales\Model\Order\Email\Sender\InvoiceSender')->send($invoice);
                    //Send Invoice mail to customer
                    $item->addStatusHistoryComment(__('Notified customer about invoice creation #%1.', $invoice->getId()))
                    ->setIsCustomerNotified(true)
                    ->save();
                    $response['redirect_url'] = 'checkout/onepage/success';
                }else{
                    // Set redirect message
                    $this->getLydiaSession()->setRedirectTitle(('Your order can not be invoiced'));
                    $this->getLydiaSession()->setRedirectMessage(__('The payment platform has been done but your invoice has not been generated. <br/> Your Invoice is probably already invoiced , please check your email and your Customer account. <br/> Please contact support team'));
                    // Set redirect URL
                    $response['redirect_url'] = '*/*/failure';
                }
            }

        }else{
             // Treat response
             $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
             $orders = $objectManager->create('Magento\Sales\Model\ResourceModel\Order\CollectionFactory')->create($customerId)
             ->addFieldToSelect('*')
             ->addFieldToFilter('lydia_payment_id',
                     ['eq' => $_REQUEST["uniqueID"]]
                 );
            foreach($orders as $item){
                if ($item->getId()) {
                    if ($item->canCancel()) {
                        try {
                            $item->registerCancellation($errorMessage)->save();
                        } catch (LocalizedException $e) {
                            $this->logger->critical($e);
                        } catch (\Exception $e) {
                            $this->logger->critical($e);
                            $errorMessage .= '<br/><br/>';
                            $errorMessage .= __('The order has not been cancelled.') . ' : ' . $e->getMessage();
                            $item->addStatusHistoryComment($errorMessage)->save();
                        }
                    } else {
                        $errorMessage .= '<br/><br/>';
                        $errorMessage .= __('The order was already cancelled.');
                        $item->addStatusHistoryComment($errorMessage)->save();
                    }
                }
                $this->getLydiaSession()->setRedirectTitle(('Your payment has been rejected'));
                $this->getLydiaSession()->setRedirectMessage(__('The payment platform has rejected your transaction. Please try again later'));
                $response['redirect_url'] = '*/*/failure';
            }
        }


        // Save Lydia response in session
        $this->getLydiaSession()->setResponse($response);

        $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath($response['redirect_url']);
        return $resultRedirect;
    }
}
