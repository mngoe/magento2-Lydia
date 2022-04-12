<?php 

namespace Ynote\Lydia\Cron;
use Magento\Sales\Model\Order;

class CheckLydiaPayment
{
    public function execute()
    {
        //your cron job code
        $yesterday = new \DateTime();
        $yesterday = $yesterday->modify("-1 day");
        var_dump($yesterday->format('Y-m-d H:i:s'));
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $orders = $objectManager->create('Magento\Sales\Model\ResourceModel\Order\CollectionFactory')->create($customerId)
        ->addFieldToSelect('*')
        ->addFieldToFilter('lydia_payment_id',
             ['neq' => 'NULL']
        )
        ->addFieldToFilter('created_at', array('gteq' => $yesterday->format('Y-m-d H:i:s')));
        foreach($orders as $item){
            $config = $objectManager->create('Ynote\Lydia\Model\Config')->initMethod('lydia_standard');
            $stateParams = array('order_ref'=>$item["lydia_payment_id"], 
                'vendor_token'=>$config->getConfigData('vendor_token', 'lydia_standard'));
            $response = $objectManager->create('Ynote\Lydia\Helper\Data')->processGatewayCall($stateParams,'api/request/state.json');
            if($response ["data"]->state == 1){
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
                    $objectManager->create('Psr\Log\LoggerInterface')->critical("Creation de facture ".$item["lydia_payment_id"]);
                }else{
                    var_dump("Facture :".$item["lydia_payment_id"]." ---- Deja facturé.");
                    $objectManager->create('Psr\Log\LoggerInterface')->critical("Facture :".$item["lydia_payment_id"]." ---- Deja facturé.");
                }
            }
           
        }
        var_dump("=========================");
    }
}