<?php
namespace Ynote\Lydia\Setup;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Sales\Setup\SalesSetupFactory;

/**
* Class UpgradeData*/

class UpgradeData implements UpgradeDataInterface{

    private $salesSetupFactory;
    public function __construct(SalesSetupFactory $salesSetupFactory){
        $this->salesSetupFactory = $salesSetupFactory;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context) {
        if ($context->getVersion()
            && version_compare($context->getVersion(), '3.0.5') < 0
        ){
            echo "Upgrade Lydia";
            $salesSetup = $this->salesSetupFactory->create(['setup' => $setup]);
            $salesSetup->addAttribute(
                'order',
                'lydia_payment_id',
                [
                    'type' => 'varchar',
                    'length' => 255,
                    'visible' => true,
                    'required' => false,
                    'grid' => false
                ]
            );
        }
        if ($context->getVersion()
        && version_compare($context->getVersion(), '3.0.6') < 0
        ){
            echo "Upgrade Lydia";
            $salesSetup = $this->salesSetupFactory->create(['setup' => $setup]);
            $salesSetup->addAttribute(
                'order',
                'lydia_request_id',
                [
                    'type' => 'varchar',
                    'length' => 255,
                    'visible' => true,
                    'required' => false,
                    'grid' => false
                ]
            );
        }
    }
}