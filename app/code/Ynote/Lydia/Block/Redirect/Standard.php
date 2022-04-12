<?php

namespace Ynote\Lydia\Block\Redirect;
use Magento\Framework\View\Element\Template;

class Standard extends Template
{

    protected $standardMethod;

    public function __construct(
        \Ynote\Lydia\Model\Method\Standard $standard,
        \Magento\Framework\View\Element\Template\Context $context,
         array $data = []
    ) {
        $this->standardMethod = $standard;
        parent::__construct($context, $data);
    }
    public function getBankForm($param,$url){
        $html = "<form id='form_lydia_api' method='post' action='".$url."' style='display:none'>";
        foreach($param as $key=>$value){
            $html .= "<input type='hidden' value='".$value."' name='".$key."'>";
        }
        $html .= "</form>";
        return $html;

    }

}
