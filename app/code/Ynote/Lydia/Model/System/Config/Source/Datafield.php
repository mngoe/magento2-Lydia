<?php
namespace Ynote\Lydia\Model\System\Config\Source;

class Datafield implements \Magento\Framework\Option\ArrayInterface
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

    public function toOptionArray()
    {
        $options = [];

        foreach ($this->atosConfig->getDataFieldKeys() as $code => $name) {
            $options[] = [
                'value' => $code,
                'label' => $name
            ];
        }

        return $options;
    }
}
