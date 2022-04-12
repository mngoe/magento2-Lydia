<?php
namespace Ynote\Lydia\Block\Form;
use Magento\Payment\Block\Form;
class Standard extends Form
{

    protected function _construct()
    {
        $this->setTemplate('Ynote_Lydia::atos/form/standard.phtml');
        parent::_construct();
    }

    public function getCreditCardsAccepted()
    {
        return ['CB','VISA','MASTERCARD'];
    }


}
