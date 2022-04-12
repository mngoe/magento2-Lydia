<?php

namespace Ynote\Lydia\Model\System\Config\Source;

class SIPSVersions implements \Magento\Framework\Data\OptionSourceInterface
{

    /**
     * @var \Ynote\Lydia\Model\Config
     */
    protected $atosConfig;

    /**
     * Datafield constructor.
     * @param \Ynote\Lydia\Model\Config $config
     */
    public function __construct(
        \Ynote\Lydia\Model\Config $config
    ) {
        $this->atosConfig = $config;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        $configModel = $this->atosConfig;
        return $configModel->getSIPSVersionOptions();
    }
}

