<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ynote\Lydia\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Session\SessionManagerInterface;

/**
 * Class ConfigProvider
 */
final class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'lydia_standard';

    protected $_config;

    /*
     * @var SessionManagerInterface
     */
    protected $session;

    /**
     * ConfigProvider constructor.
     * @param \Ynote\Lydia\Model\Config $config
     * @param SessionManagerInterface $session
     */
    public function __construct(
        \Ynote\Lydia\Model\Config $config,
        SessionManagerInterface $session
    ) {
        $this->_config = $config;
        $this->session = $session;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        $storeId = $this->session->getStoreId();
        return [
            'payment' => [
                self::CODE => [
                    'merchantId' => $this->_config->getMerchantId(),

                ]
            ]
        ];
    }
}
