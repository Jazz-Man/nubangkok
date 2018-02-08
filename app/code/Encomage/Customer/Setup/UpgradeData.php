<?php
namespace Encomage\Customer\Setup;

use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetup;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

class UpgradeData implements UpgradeDataInterface
{


    private $customerSetupFactory;

    public function __construct(CustomerSetupFactory $customerSetupFactory)
    {
        $this->customerSetupFactory = $customerSetupFactory;
    }


    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $dbVersion = $context->getVersion();

//        if (version_compare($dbVersion, '0.0.2', '<')) {
            /** @var CustomerSetup $customerSetup */
            $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
//            $customerSetup->addAttribute(
//                Customer::ENTITY,
//                'example',
//                [
//                    'label' => 'Example Attribute',
//                    'required' => 0,
//                    'system' => 0, // <-- important, otherwise values aren't saved.
//                    // @see Magento\Customer\Model\Metadata\CustomerMetadata::getCustomAttributesMetadata()
//                    'position' => 100
//                ]
//            );
            $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'telephone')
                ->setData('used_in_forms', ['customer_account_create','adminhtml_customer'])
                ->save();
//        }
    }
}