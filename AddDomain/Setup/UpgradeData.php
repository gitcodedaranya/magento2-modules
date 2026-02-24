<?php
namespace Ime\AddDomain\Setup;

use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Customer\Setup\CustomerSetupFactory;

class UpgradeData implements UpgradeDataInterface
{
    protected $customerSetupFactory;

    public function __construct(CustomerSetupFactory $customerSetupFactory)
    {
        $this->customerSetupFactory = $customerSetupFactory;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if ($context->getVersion()
            && version_compare($context->getVersion(), '1.0.3', '<')
        ) {
            $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

            $customerSetup->addAttribute(
                Customer::ENTITY,
                'is_approve_customer',
                [
                    'label'        => 'Approve',
                    'input'        => 'boolean',
                    'type'         => 'int',
                    'source'       => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
                    'required'     => false,
                    'visible'      => true,
                    'user_defined' => true,
                    'system'       => 0,
                    'global'       => ScopedAttributeInterface::SCOPE_GLOBAL
                ]
            );

            $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'verify_domain');
            $attribute->setData('used_in_forms', ['adminhtml_customer']);
            $attribute->save();
        }

         if ($context->getVersion()
            && version_compare($context->getVersion(), '1.0.12', '<')
        ) {
            $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

            $customerSetup->addAttribute(
                Customer::ENTITY,
                'is_approve_customer',
                [
                    'label'        => 'Approve',
                    'input'        => 'boolean',
                    'type'         => 'int',
                    'source'       => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
                    'required'     => false,
                    'visible'      => true,
                    'user_defined' => true,
                    'system'       => 0,
                    'global'       => ScopedAttributeInterface::SCOPE_GLOBAL
                ]
            );

            $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'is_approve_customer');
            $attribute->setData('used_in_forms', ['adminhtml_customer']);
            $attribute->save();
        }

        $setup->endSetup();
    }
}
