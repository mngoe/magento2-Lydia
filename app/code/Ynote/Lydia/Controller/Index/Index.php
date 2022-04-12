<?php
namespace Ynote\Lydia\Controller\Index;

use Ynote\Lydia\Model\Api\Request;
use Ynote\Lydia\Model\Api\Response;
use Ynote\Lydia\Model\Config;
use Ynote\EdiSync\Helper\Data;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\LocalizedException;

class Index extends \Magento\Framework\App\Action\Action
{

    /* @var \Magento\Checkout\Model\Session */
    protected $checkoutSession;

    /* @var \Magento\Quote\Model\QuoteFactory */
    protected $quoteFactory;

    /* @var \Magento\Quote\Model\QuoteRepository */
    protected $quoteRepository;

    /* @var \Magento\Sales\Model\Order */
    protected $orderInterface;

    /**
    * @var \Ynote\Lydia\Model\Config
    */
    protected $_config;

    /**
     * @var \Ynote\Lydia\Model\Api\Request
     */
    protected $_requestApi;

    /*
     *  @var \Ynote\Lydia\Model\Method\Standard
     */
    protected $_standardMethod;

    /* @var \Magento\Customer\Model\Session $customerSession */
    protected $customerSession;

    /**
     * @var \Ynote\Lydia\Model\Api\Response
     */
    protected $_responseApi;

    /**
     * @var \Ynote\Lydia\Model\Session
     */
    protected $lydiaSession;

    /**
     * @var \Psr\Log\LoggerInterface $logger
     */
    protected $logger;

    /*
     * @var \Ynote\Lydia\Helper\Data
     */
    protected $lydiaHelper;

    /*
     *  @var \Ynote\Lydia\Model\Ipn $atosIpn
     */
    protected $atosIpn;

    protected $_blockFactory;

    /** @var \Magento\Framework\View\Result\PageFactory $resultPageFactory **/
    protected $resultFactory;

    protected $searchCriteriaBuilder;
    protected $orderRepository;

    /**
     * Index constructor.
     * @param \Magento\Framework\Module\Dir\Reader $moduleDirReader
     * @param \Ynote\Lydia\Model\Api\Files $filesApi
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Ynote\Lydia\Model\Adminhtml\System\Config\Source\Payment\Cctype $ccType
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Config $config
     * @param Request $requestApi
     * @param Response $responseApi
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
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
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
        $this->moduleDirReader = $moduleDirReader;
        $this->filesApi = $filesApi;
        $this->scopeConfig = $scopeConfig;
        $this->ccType = $ccType;
        $this->storeManager = $storeManager;
        $this->_config = $config;
        $this->_requestApi = $requestApi;
        $this->_responseApi = $responseApi;
        $this->checkoutSession = $checkoutSession;
        $this->quoteFactory = $quoteFactory;
        $this->quoteRepository = $quoteRepository;
        $this->orderInterface = $orderInterface;
        $this->customerSession = $customerSession;
        $this->lydiaSession = $lydiaSession;
        $this->logger = $logger;
        $this->lydiaHelper = $lydiaHelper;
        $this->_standardMethod = $standardMethod;
        $this->atosIpn = $atosIpn;
        $this->_blockFactory = $blockFactory;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Get Atos Api Response Model
     * @return \Ynote\Lydia\Model\Api\Response
     *
     */
    public function getApiResponse()
    {
        return $this->_responseApi;
    }

    /**
     * Get Lydia Standard config
     *
     * @return \Ynote\Lydia\Model\Config
     */
    public function getConfig()
    {
        return $this->_config;
    }

    public function _getLydiaResponse($idTransaction){
        var_dump($idTransaction);
    }

    /**
     * Get checkout session
     *
     * @return  \Magento\Checkout\Model\Session
     */
    public function getCheckoutSession()
    {
        return $this->checkoutSession;
    }

    /**
     * Get customer session
     *
     * @return \Magento\Customer\Model\Session
     */
    public function getCustomerSession()
    {
        return $this->customerSession;
    }

    /**
     * Get Lydia Standard session
     *
     * @return \Ynote\Lydia\Model\Session
     */
    public function getLydiaSession()
    {
        return $this->lydiaSession;
    }

    protected function getMethodInstance()
    {
        return $this->_standardMethod;
    }
    /**
     * Treat Lydia response
     */
    protected function _Response($data, $options = null)
    {
        $response = [];

        if($options == null) {
            $response = $this->getApiResponse()->doResponse($data, [
                'bin_response' => $this->_config->getBinResponse(),
                'pathfile' => $this->_config->getPathfile()
            ]);
        }else{
            $response = $this->getApiResponse()->doResponsev2($data, $options);
        }

        //die('Hash code: '.isset($response['hash']['code']).' reponse is: '.print_r($response, 1));
        if (!isset($response['hash']['code'])) {
            $this->_redirect('*/*/failure');
            return;
        }

        if ($response['hash']['code'] == '-1') {
            $this->_redirect('*/*/failure');
            return;
        }

        return $response;
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        // TODO: Implement execute() method.
    }

}
