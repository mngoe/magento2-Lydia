<?php
namespace Ynote\Lydia\Controller\Payment;

use Ynote\Lydia\Controller\Index\Index;
use Magento\Framework\View\Result\PageFactory;

class Failure extends Index
{
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
        \Ynote\Lydia\Helper\Data $atosHelper,
        \Ynote\Lydia\Model\Method\Standard $standardMethod,
        \Ynote\Lydia\Model\Ipn $atosIpn,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Element\BlockFactory $blockFactory,
        PageFactory  $resultPageFactory
    ) {
        parent::__construct(
            $moduleDirReader,
            $filesApi,
            $scopeConfig,
            $ccType,
            $storeManager,
            $config,
            $requestApi,
            $responseApi,
            $checkoutSession,
            $quoteFactory,
            $quoteRepository,
            $orderInterface,
            $customerSession,
            $lydiaSession,
            $logger,
            $atosHelper,
            $standardMethod,
            $atosIpn,
            $context,
            $blockFactory,
            $resultPageFactory
        );
    }
    /**
     * Dispatch request
     * When Lydia returns
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getLayout()->getBlock('lydiafailure')->setTitle($this->getLydiaSession()->getRedirectTitle());
        $resultPage->getLayout()->getBlock('lydiafailure')->setMessage($this->getLydiaSession()->getRedirectMessage());
        $this->getLydiaSession()->unsetAll();
        return $resultPage;
    }
}
