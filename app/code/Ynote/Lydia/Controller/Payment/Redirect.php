<?php
/**
 * Created by IntelliJ IDEA.
 * User: madalien
 * Date: 8/17/17
 * Time: 1:32 PM
 */

namespace Ynote\Lydia\Controller\Payment;

use Ynote\Lydia\Controller\Index\Index;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\Data\OrderStatusHistoryInterface;
use Magento\Sales\Api\OrderStatusHistoryRepositoryInterface;

define('_RECIPIENT_TYPE_', 'email');
define('_DISPLAY_CONFIRMATION_', 'no');

class Redirect extends Index
{
        /**
     * @var \Ynote\Lydia\Model\Config
     */
    protected $_config;
    protected $standardMethod;

    
    /** @var OrderStatusHistoryRepositoryInterface */
    private $orderStatusRepository;

    /** @var OrderRepositoryInterface */
    protected $orderRepository;

    public function __construct(
        \Ynote\Lydia\Model\Ipn $ipnService,
        \Magento\Framework\Module\Dir\Reader $moduleDirReader,
        \Ynote\Lydia\Model\Api\Files $filesApi,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Ynote\Lydia\Model\Adminhtml\System\Config\Source\Payment\Cctype $ccType,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Ynote\Lydia\Model\Config $_config,
        \Ynote\Lydia\Model\Api\Request $requestApi,
        \Ynote\Lydia\Model\Api\Response $responseApi,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Quote\Model\QuoteRepository $quoteRepository,
        \Magento\Sales\Model\Order $orderInterface,
        \Magento\Customer\Model\Session $customerSession,
        \Ynote\Lydia\Model\Session $lydiaSession,
        \Psr\Log\LoggerInterface $logger,
        \Ynote\Lydia\Helper\Data $lydiaHelper,
        \Ynote\Lydia\Model\Method\Standard $standardMethod,
        \Ynote\Lydia\Model\Ipn $atosIpn,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Element\BlockFactory $blockFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        OrderStatusHistoryRepositoryInterface $orderStatusRepository,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->ipnService = $ipnService;
        $this->_config = $_config;
        $this->standardMethod = $standardMethod;
        parent::__construct(
            $moduleDirReader,
            $filesApi,
            $scopeConfig,
            $ccType,
            $storeManager,
            $_config,
            $requestApi,
            $responseApi,
            $checkoutSession,
            $quoteFactory,
            $quoteRepository,
            $orderInterface,
            $customerSession,
            $lydiaSession,
            $logger,
            $lydiaHelper,
            $standardMethod,
            $atosIpn,
            $context,
            $blockFactory,
            $resultPageFactory
        );
    }

    /**
     * Dispatch request
     * When a customer chooses Lydia Standard on Checkout/Payment page
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $valueUniq = md5($this->getCheckoutSession()->getLastRealOrder()->getQuoteId()."&".strtotime(date("Y-m-d H:i:s")));
        $params = array();
        $return = array();
        $params['vendor_token'] = $this->_config->getConfigData('vendor_token', 'lydia_standard');
        $params['type'] = _RECIPIENT_TYPE_;
        $params['display_confirmation'] = _DISPLAY_CONFIRMATION_;
        $params['delayed_payment'] = 1;
        $errors = array();
        $values = array('params'=>$params,'errors'=>$errors);
        $values = $this->lydiaHelper->setParamValue('email', $values, $this->getCheckoutSession()->getLastRealOrder()->getCustomerEmail(), false, 'recipient', '');
		$values = $this->lydiaHelper->setParamValue('currency', $values, $this->_config->getConfigData('gateway_currency', 'lydia_standard'), false, '', '');
		$values = $this->lydiaHelper->setParamValue('amount', $values, $this->getCheckoutSession()->getLastRealOrder()->getBaseGrandTotal(), false, '', '');
		$values = $this->lydiaHelper->setParamValue('order_ref', $values, $valueUniq, false, 'order_ref', '');
		$values = $this->lydiaHelper->setParamValue('success_url', $values, $this->standardMethod->_getNormalReturnUrl()."?uniqueID=".$valueUniq, false, 'browser_success_url', '');
		$values = $this->lydiaHelper->setParamValue('fail_url', $values, $this->standardMethod->_getCancelReturnUrl(), false, 'browser_fail_url', '');
		$values = $this->lydiaHelper->setParamValue('end_mobile_url', $values, $values['params']['browser_success_url'], true, '', '');
		$values = $this->lydiaHelper->setParamValue('confirm_url', $values, null, true, '', '');
		$values = $this->lydiaHelper->setParamValue('message', $values, null, true, '', '');
		$values = $this->lydiaHelper->setParamValue('notify', $values, 'no', true, 'notify', '');
		$values = $this->lydiaHelper->setParamValue('notify_collector', $values, null, true, 'notify_collector', '');
		$values = $this->lydiaHelper->setParamValue('payment_mail_description', $values, null, true, 'payment_mail_description', '');
		$values = $this->lydiaHelper->setParamValue('collecter_receipt_description', $values, null, true, 'collecter_receipt_description', '');

        if(empty($values['errors'])){
            $callResult = $this->lydiaHelper->processGatewayCall($values['params'], 'api/request/do.json');
            if(!isset($callResult['errors']) || empty($callResult['errors'])){
                if(($callResult['data']->error=='0') && !empty($callResult['data']->mobile_url)){
                    $data = $callResult['data'];
                }else{
                    $values['errors'][] = $callResult['data']->message;
                }
            }else{
                $values['errors'] = $callResult['errors'];
            }

            if(empty($values['errors']) && isset($data)){
                $stateParams = array('order_ref'=>$valueUniq, 'vendor_token'=>$params['vendor_token']);
                $callResult = $this->lydiaHelper->processGatewayCall($stateParams, 'api/request/state.json');

                if(!isset($callResult['errors']) || empty($callResult['errors'])){
                    if(isset($callResult['data']->state) && ($callResult['data']->state==0) &&
                        isset($callResult['data']->signature) ){
                        if($this->lydiaHelper->getValue('no_redirect')){
                            $return['payment_url'] = $data->mobile_url;
                        }else{
                            $return['payment_url'] = $data->mobile_url;
                        }
                         // Save Lydia Payment ID
                        $this->getCheckoutSession()->getLastRealOrder()->setLydiaPaymentId($valueUniq);
                        $this->getCheckoutSession()->getLastRealOrder()->setLydiaRequestId($requestUid);
                        // Set Order Comment History
                        $comment = $this->getCheckoutSession()->getLastRealOrder()->addStatusHistoryComment(
                            'Sent Customer to Lydia. Lydia Paiement ID : Unique ID - '.$valueUniq.' / RequestID : '.$requestUid
                        );

                        try {
                            $orderSave = $this->getCheckoutSession()->getLastRealOrder()->save();
                        } catch (\Exception $exception) {
                            $this->logger->critical($exception->getMessage());
                        }
                    }else{
                        $values['errors'][] = 'Unable to confirm payment state';
                    }
                }else{
                    $values['errors'] = $callResult['errors'];
                }
            }
        }else{
            $this->logger->critical($values['errors']);
        }

        $return['errors'] = $values['errors'];
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getLayout()->getBlock('lydiaredirect')->setData('form_url', $return['payment_url']);
        $resultPage->getLayout()->getBlock('lydiaredirect')->setData('form_data', $params);
        $this->getLydiaSession()->setQuoteId($this->getCheckoutSession()->getLastRealOrder()->getQuoteId());
       
        $this->getCheckoutSession()->unsQuoteId();
        $this->getCheckoutSession()->unsRedirectUrl();
        return $resultPage;
    }
    
}
