<?php

namespace Encomage\Careers\Model\Careers;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Encomage\Careers\Model\ResourceModel\Careers\CollectionFactory;

class DataProvider extends AbstractDataProvider
{
    /**
     * @var
     */
    private $loadedData;

    /**
     * Edit constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param \Encomage\Careers\Model\ResourceModel\Careers\CollectionFactory $contactCollectionFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $contactCollectionFactory,
        array $meta = [],
        array $data = []
    )
    {
        $this->collection = $contactCollectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        foreach ($items as $career) {
            $this->loadedData[$career->getId()] = $career->getData();
        }
        return $this->loadedData;
    }
}