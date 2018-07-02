<?php
/**
 * @author Andrey Bondarenko
 * @link http://encomage.com
 * @mail info@encomage.com
 */
namespace Encomage\StoreLocator\Ui\Component\DataProvider;

/**
 * Class Edit
 * @package Encomage\StoreLocator\Ui\Component\DataProvider
 */
class Edit extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var
     */
    protected $_loadedData;

    /**
     * Edit constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param \Encomage\StoreLocator\Model\ResourceModel\Marker\CollectionFactory $markersCollectionFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Encomage\StoreLocator\Model\ResourceModel\Marker\CollectionFactory $markersCollectionFactory,
        array $meta = [],
        array $data = []
    )
    {
        $this->collection = $markersCollectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * @return array
     */
    public function getData()
    {
        if (!isset($this->_loadedData)) {
            $items = $this->collection->getItems();
            foreach ($items as $marker) {
                $this->_loadedData[$marker->getId()] = $this->_prepareData($marker->getData());
            }
        }
        return $this->_loadedData;
    }

    /**
     * @param array $data
     * @return array
     */
    protected function _prepareData(array $data)
    {
        (isset($data['longitude']) && isset($data['latitude']))
            ? $data['coordinates'] = $data['latitude'] . ':' . $data['longitude']
            : $data['coordinates'] = '';
        return $data;
    }
}
