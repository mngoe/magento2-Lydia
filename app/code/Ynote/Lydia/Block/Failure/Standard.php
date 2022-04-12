<?php

namespace Ynote\Lydia\Block\Failure;
use Magento\Framework\View\Element\Template;
class Standard extends Template
{

    protected $standardMethod;

    /**
     * @var \Ynote\Lydia\Model\Session
     */
    protected $lydiaSession;


    protected $title;
    protected $message;




    /**
     * Standard constructor.
     * @param \Ynote\Lydia\Model\Method\Standard $standard
     * @param \Ynote\Lydia\Model\Session $lydiaSession
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Ynote\Lydia\Model\Method\Standard $standard,
        \Ynote\Lydia\Model\Session $lydiaSession,
        \Magento\Framework\View\Element\Template\Context $context,
         array $data = []
    ) {
        $this->standardMethod = $standard;
        $this->lydiaSession = $lydiaSession;
        $this->title = $this->lydiaSession->getRedirectTitle();
        $this->message = $this->lydiaSession->getRedirectMessage();
        $this->lydiaSession->unsetAll();
        parent::__construct($context, $data);
    }





    public function getTitle()
    {
        return $this->title;
    }

    public function getMessage()
    {
        return $this->message;
    }

}
