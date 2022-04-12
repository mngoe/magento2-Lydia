<?php
namespace Ynote\Lydia\Controller\Payment;

use Ynote\Lydia\Controller\Index\Index;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;

class AutoResponse extends Index
{

    /*
     * @var \Ynote\Lydia\Model\Ipn
     */
    protected $ipnService;

    /**
     * AutoResponse constructor.
     * @param \Magento\Framework\Module\Dir\Reader $moduleDirReader
     * @param \Ynote\Lydia\Model\Api\Files $filesApi
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Ynote\Lydia\Model\Adminhtml\System\Config\Source\Payment\Cctype $ccType
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Ynote\Lydia\Model\Config $config
     * @param \Ynote\Lydia\Model\Api\Request $requestApi
     * @param \Ynote\Lydia\Model\Api\Response $responseApi
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param \Magento\Quote\Model\QuoteRepository $quoteRepository
     * @param \Magento\Sales\Model\Order $orderInterface
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Ynote\Lydia\Model\Session $lydiaSession
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Ynote\Lydia\Helper\Data $lydiaHelper
     * @param \Ynote\Lydia\Model\Method\Standard $standardMethod
     * @param \Ynote\Lydia\Model\Ipn $atosIpn
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Element\BlockFactory $blockFactory
     * @param PageFactory $resultPageFactory
     * @param \Ynote\Lydia\Model\Ipn $ipnService
     */
    public function __construct(
        \Ynote\Lydia\Model\Ipn $ipnService,
        \Magento\Framework\Module\Dir\Reader $moduleDirReader,
        \Ynote\Lydia\Model\Api\Files $filesApi,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Ynote\Lydia\Model\Adminhtml\System\Config\Source\Payment\Cctype $ccType,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Ynote\Lydia\Model\Config $config,
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
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->ipnService = $ipnService;
        parent::__construct(
            $moduleDirReader,
            $filesApi,
            $scopeConfig,
            $ccType,
            $storeManager,
            $config,
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
     * When Lydia returns
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $options = [];
        if (!(array_key_exists('DATA', $_REQUEST) || array_key_exists('Data', $_REQUEST))) {
            // Log error
            $errorMessage = __('Automatic response received but no data received for order #%1.', $this->getCheckoutSession()->getLastRealOrderId());
            $this->lydiaHelper->logError(get_class($this), __FUNCTION__, $errorMessage);
            $this->getResponse()->setHeader('HTTP/1.1', '503 Service Unavailable');
            return;
        }

        if(array_key_exists('Seal', $_REQUEST)) {
            $options['Seal'] = $_REQUEST['Seal'];
            $options['Data'] = $_REQUEST['Data'];
            $options['Encode'] = $_REQUEST['Encode'];
            $options['InterfaceVersion'] = $_REQUEST['InterfaceVersion'];
            $this->ipnService->processIpnResponse($_REQUEST['Data'], $this->getMethodInstance(), $options);
        }else {
            $this->ipnService->processIpnResponse($_REQUEST['DATA'], $this->getMethodInstance());
        }
    }

}
