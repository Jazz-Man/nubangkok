<?php
/**
 * @author Andrey Bondarenko
 * @link http://encomage.com
 * @mail info@encomage.com
 */

namespace Encomage\StoreLocator\Model\ResourceModel\Marker;

/**
 * Class Collection
 * @package Encomage\StoreLocator\Model\ResourceModel\Marker
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var \Encomage\StoreLocator\Helper\Config
     */
    protected $_helperConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Collection constructor.
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Encomage\StoreLocator\Helper\Config $config
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null $resource
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Encomage\StoreLocator\Helper\Config $config,
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null

    )
    {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->_storeManager = $storeManager;
        $this->_helperConfig = $config;
    }

    /**
     * Class construct
     */
    protected function _construct()
    {
        $this->_init(
            'Encomage\StoreLocator\Model\Marker',
            'Encomage\StoreLocator\Model\ResourceModel\Marker'
        );
    }

    /**
     * @param null $storeId
     * @return $this
     */
    public function getDataByStore($storeId = null)
    {
        if (!$storeId) {
            $storeId = $this->_storeManager->getStore()->getId();
        }
        $fields = ['store_id'];
        $conditions = [
            [
                'like' => '%' . (int)$storeId . '%'
            ]
        ];
        if ($storeId != \Encomage\StoreLocator\Model\Config\Source\Markers::ALL_STORE_VIEWS) {
            $fields[] = 'store_id';
            $conditions[] = [
                'like' => '%' . \Encomage\StoreLocator\Model\Config\Source\Markers::ALL_STORE_VIEWS . '%'
            ];
        }
        $this->addFieldToFilter($fields, $conditions);
        return $this;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function toOptionArray()
    {
        return parent::_toOptionArray($this->getResource()->getIdFieldName());
    }
}