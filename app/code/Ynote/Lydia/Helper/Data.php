<?php

namespace Ynote\Lydia\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{


      /* @var \Magento\Checkout\Model\Session */
    protected $checkoutSession;

    /* @var \Magento\Quote\Model\QuoteFactory */
    protected $quoteFactory;

    /* @var \Magento\Sales\Model\Order */
    protected $orderInterface;

    /**
    * @var \Ynote\Lydia\Model\Config
    */
   protected $_config;

   protected $scopeConfig;
   /**
    * @var \Ynote\Lydia\Model\Api\Request
    */
   protected $_requestApi;

   /* @var \Magento\Customer\Model\Session $customerSession */
   protected $customerSession;

   /**
    * @var \Ynote\Lydia\Model\Api\Response
    */
   protected $_responseApi ;


    /**
     * @var \Ynote\Lydia\Model\Session
     */
    protected $lydiaSession;

    /**
     * @var \Psr\Log\LoggerInterface $logger
     */
    protected $logger;

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
        \Magento\Sales\Model\Order $orderInterface,
        \Magento\Customer\Model\Session $customerSession,
        \Ynote\Lydia\Model\Session $lydiaSession,
        \Psr\Log\LoggerInterface $logger
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
        $this->orderInterface = $orderInterface;
        $this->customerSession = $customerSession;
        $this->lydiaSession = $lydiaSession;
        $this->logger = $logger;
    }

    public function createSignature($param,$gatewaytoken){
        ksort($param);
		return md5(http_build_query($param).'&'.$gatewaytoken);
    }

	public function processGatewayCall($param, $uri){
        $return = array();
        $curl = curl_init($this->_config->getConfigData('lydia_url', 'lydia_standard').$uri);
		curl_setopt($curl,CURLOPT_POST, true);
		curl_setopt($curl,CURLOPT_POSTFIELDS, $param);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($curl);
		curl_close($curl);
		if(!empty($result)){
			$return['data']= json_decode($result, false, 512, JSON_BIGINT_AS_STRING);
		}else{
			$return['errors'][] = $this->l('An error occured while connecting to Payment server');
		}
		return $return;
    }

    public function setParamValue($getKey, $params, $defaultValue = null, $optionnal = false, $setKey = '', $errorMessage =''){
        $setKey = empty($setKey) ? $getKey : $setKey;
        $value = $this->getValue($getKey);
        if(($value!==null) || ($defaultValue!==null)){
            $params['params'][$setKey] = ($value!==null) ? $value : $defaultValue;
        }elseif(!$optionnal){
            $params['errors'][] = empty($errorMessage) ? 'param "'.$getKey.'" is required' : $errorMessage;
        }
        return $params;
    }

    public function getValue($key){
        $return = (isset($_POST[$key]) ? $_POST[$key] : (isset($_GET[$key]) ? $_GET[$key] : null));
        return $return;
    }


    /**
     * Log Error
     *
     * @param string $class
     * @param string $function
     * @param string $message
     */
    public function logError($class, $function, $message)
    {
        $this->logger->error($class . ' ' . $function . ': ' . $message);
    }

}
