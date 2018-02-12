<?php

namespace Encomage\Customer\Setup;

use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Eav\Model\Entity\Attribute\Set as AttributeSet;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var EavSetupFactory
     */
    protected $customerSetupFactory;

    /**
     * @var AttributeSetFactory
     */
    private $attributeSetFactory;

    /**
     * @var CustomerSetupFactory
     */
    protected $customerEavSetupFactory;


    public function __construct(EavSetupFactory $eavSetupFactory,
                                AttributeSetFactory $attributeSetFactory,
                                CustomerSetupFactory $customerEavSetupFactory)
    {
        $this->customerSetupFactory = $eavSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->customerEavSetupFactory = $customerEavSetupFactory;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '0.0.3', '<')) {
            $setup->startSetup();
            /** @var CustomerSetup $customerSetup */
            $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
            $customerSetup->updateAttribute(
                'customer_address',
                'street',
                'is_required',
                false
            );
            $customerSetup->updateAttribute(
                'customer_address',
                'city',
                'is_required',
                false
            );
            $setup->endSetup();
            $this->setLineId($setup);
        }
    }


    protected function setLineId($setup)
    {
        $customerSetup = $this->customerEavSetupFactory->create(['setup' => $setup]);

        $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();

        /** @var $attributeSet AttributeSet */
        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

        $customerSetup->addAttribute(Customer::ENTITY, 'line_id', [
            'type' => 'varchar',
            'label' => 'Line Id',
            'input' => 'text',
            'required' => false,
            'visible' => true,
            'user_defined' => true,
            'position' => 200,
            'system' => 0,
        ]);

        $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'line_id')
            ->addData([
                'attribute_set_id' => $attributeSetId,
                'attribute_group_id' => $attributeGroupId,
                'used_in_forms' => ['customer_account_create', 'adminhtml_customer'],
            ]);

        $attribute->save();
    }


}