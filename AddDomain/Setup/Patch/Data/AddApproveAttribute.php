<?php
namespace Ime\AddDomain\Setup\Patch\Data;

use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean as BooleanSource;

class AddApproveAttribute implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var CustomerSetupFactory
     */
    private $customerSetupFactory;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CustomerSetupFactory $customerSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->customerSetupFactory = $customerSetupFactory;
    }

    public function apply()
    {
        $this->moduleDataSetup->startSetup();

        /** @var \Magento\Customer\Setup\CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);

        // safe remove if attribute already exists (so re-run won't throw)
        try {
            $customerSetup->removeAttribute('customer', 'is_approved_customer');
        } catch (\Exception $e) {
            // ignore if not exists
        }

        // add attribute
        $customerSetup->addAttribute(
            'customer',
            'is_approved_customer',
            [
                'type' => 'int',
                'label' => 'Approve Customer',
                // 'input' is legacy EAV input, keep boolean to use Boolean source
                'input' => 'boolean',
                'source' => BooleanSource::class,
                'required' => false,
                'visible' => true,
                'user_defined' => true,
                'system' => false,
                'position' => 200,
                'default' => 0,
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,

                // UI form configuration (required to avoid the "formElement" error)
                'dataType' => 'boolean',      // data type for UI components
                'formElement' => 'select',    // form element used in UI form (select shows yes/no)
                'visible_on_front' => false,
                // grid flags (optional)
                'is_used_in_grid' => false,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
            ]
        );

        // assign attribute to adminhtml_customer form so it's visible in admin edit customer
        $attribute = $customerSetup->getEavConfig()->getAttribute('customer', 'is_approved_customer');
        $attribute->setData('used_in_forms', [
            'adminhtml_customer',
            'adminhtml_checkout',
            'customer_account_create',
            'customer_account_edit'
        ]);

        $attribute->setData('is_user_defined', 1);
        $attribute->setData('is_system', 0);

        // Assign to attribute set & group (THIS IS THE FIX)
        $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();
        $attributeGroupId = $customerSetup->getDefaultAttributeGroupId(
            $customerEntity->getEntityTypeId(),
            $attributeSetId
        );

        $customerSetup->addAttributeToSet(
            'customer',
            $attributeSetId,
            $attributeGroupId,
            'is_approved_customer'
        );

        $attribute->save();
        $this->moduleDataSetup->endSetup();
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }
}
