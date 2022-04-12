<?php

namespace Ynote\Lydia\Block\Info;
class Standard extends \Magento\Payment\Block\Info
{

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('Ynote_Lydia::atos/info/standard.phtml');
    }

    /**
     * Retrieve payment info model
     *
     * @return \Magento\Payment\Model\Info|false
     */
    public function getPaymentInfo()
    {
        // TODO: Implement getPaymentInfo() method.
    }
}
