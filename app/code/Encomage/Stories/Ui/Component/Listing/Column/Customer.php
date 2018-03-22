<?php
namespace Encomage\Stories\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\ResourceModel\Customer as ResourceCustomer;

class Customer extends Column
{
    private $customerFactory;

    private $resourceCustomer;

    public function __construct(
        ContextInterface $context,
        CustomerFactory $customerFactory,
        ResourceCustomer $resourceCustomer,
        UiComponentFactory $uiComponentFactory,
        array $components,
        array $data
    )
    {
        $this->customerFactory = $customerFactory;
        $this->resourceCustomer = $resourceCustomer;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                $customer = $this->customerFactory->create();
                $customer->load($item[$fieldName]);
                $item[$fieldName] = $customer->getName();
            }
        }

        return $dataSource;
    }
}