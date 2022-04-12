<?php

namespace Ynote\Lydia\Model;

use Magento\Payment\Model\MethodInterface;

class Config extends \Magento\Framework\DataObject
{
    const PAYMENT_ACTION_CAPTURE = 'AUTHOR_CAPTURE';
    const PAYMENT_ACTION_AUTHORIZE = 'VALIDATION';

    protected $_method;
    protected $_merchantId;
    protected $moduleDirReader;
    protected $filesApi;
    protected $scopeConfig;
    protected $ccType;
    protected $storeManager;

    /**
     * @param \Magento\Framework\Module\Dir\Reader $moduleDirReader
     */
    public function __construct(
        \Magento\Framework\Module\Dir\Reader $moduleDirReader,
        \Ynote\Lydia\Model\Api\Files $filesApi,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Ynote\Lydia\Model\Adminhtml\System\Config\Source\Payment\Cctype $ccType,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->moduleDirReader = $moduleDirReader;
        $this->filesApi = $filesApi;
        $this->scopeConfig = $scopeConfig;
        $this->ccType = $ccType;
        $this->storeManager = $storeManager;
        parent::__construct($data);
    }

    /* @return \Ynote\Lydia\Model\Config */
    public function initMethod($method)
    {
        if (empty($this->_method)) {
            $this->_method = $method;
        }
        return $this;
    }

    /**
     * Mapper from Lydia Standard payment actions to Magento payment actions
     *
     * @return string|null
     */
    public function getPaymentAction($action)
    {
        switch ($action) {
            case MethodInterface::ACTION_AUTHORIZE:
                return self::PAYMENT_ACTION_AUTHORIZE;
            case MethodInterface::ACTION_AUTHORIZE_CAPTURE:
                return self::PAYMENT_ACTION_CAPTURE;
        }
    }

    /**
     * Payment actions source getter
     *
     * @return array
     */
    public function getPaymentActions()
    {
        $paymentActions = [
            MethodInterface::ACTION_AUTHORIZE_CAPTURE => __('Author Capture'),
            MethodInterface::ACTION_AUTHORIZE => __('Validation')
        ];
        return $paymentActions;
    }


    /**
     * Get merchant country code
     *
     * @return string
     */
    public function getMerchantCountry()
    {
        $countries = $this->scopeConfig->getValue('general/country');
        $currentCountryCode = strtolower($countries['default']);
        $atosConfigCountries = $this->getMerchantCountries();

        if (count($atosConfigCountries) === 1) {
            return strtolower($atosConfigCountries[0]);
        }

        if (array_key_exists($currentCountryCode, $atosConfigCountries)) {
            $code = array_keys($atosConfigCountries);
            $key = array_search($currentCountryCode, $code);

            return strtolower($code[$key]);
        }

        return 'fr';
    }

    /**
     * Get currency code
     *
     * @return string|boolean
     */
    public function getCurrencyCode($currentCurrencyCode)
    {
        $atosConfigCurrencies = $this->getCurrencies();

        if (array_key_exists($currentCurrencyCode, $atosConfigCurrencies)) {
            return $atosConfigCurrencies[$currentCurrencyCode];
        } else {
            return false;
        }
    }

    /**
     * Get language code
     *
     * @return string
     */
    public function getLanguageCode()
    {
        $language = substr($this->scopeConfig->getValue('general/locale/code'), 0, 2);
        $atosConfigLanguages = $this->getLanguages();

        if (count($atosConfigLanguages) === 1) {
            return strtolower($atosConfigLanguages[0]);
        }

        if (array_key_exists($language, $atosConfigLanguages)) {
            $code = array_keys($atosConfigLanguages);
            $key = array_search($language, $code);

            return strtolower($code[$key]);
        }

        return 'fr';
    }

    /**
     * Get Lydia authorized languages
     *
     * @return array
     */
    public function getLanguages()
    {
        $languages = [
            "fr" => "Français",
            "en" => "Anglais"
        ];
        return $languages;
    }


    public function getConfigData($field, $paymentMethodCode, $storeId = null, $flag = false)
    {
        $path = 'payment/' . $paymentMethodCode . '/' . $field;

        if (!$flag) {
            return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
        } else {
            return $this->scopeConfig->isSetFlag($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
        }
    }
}
