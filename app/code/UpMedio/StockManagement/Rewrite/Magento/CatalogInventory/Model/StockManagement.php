<?php


namespace UpMedio\StockManagement\Rewrite\Magento\CatalogInventory\Model;


use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Model\ResourceModel\QtyCounterInterface;
use Magento\CatalogInventory\Model\ResourceModel\Stock as ResourceStock;
use Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface;
use Magento\CatalogInventory\Model\StockManagement as StockManagementAlias;
use Magento\CatalogInventory\Model\StockRegistryStorage;
use Magento\CatalogInventory\Model\StockState;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class StockManagement
 *
 * @package UpMedio\StockManagement\Rewrite\Magento\CatalogInventory\Model
 */
class StockManagement extends StockManagementAlias
{
    /**
     * @var StockRegistryStorage
     */
    private $stockRegistryStorage;
    /**
     * @var \Magento\CatalogInventory\Model\ResourceModel\QtyCounterInterface
     */
    private $qtyCounter;

    /**
     * @param ResourceStock                  $stockResource
     * @param StockRegistryProviderInterface $stockRegistryProvider
     * @param StockState                     $stockState
     * @param StockConfigurationInterface    $stockConfiguration
     * @param ProductRepositoryInterface     $productRepository
     * @param QtyCounterInterface            $qtyCounter
     * @param StockRegistryStorage|null      $stockRegistryStorage
     */
    public function __construct(
        ResourceStock $stockResource,
        StockRegistryProviderInterface $stockRegistryProvider,
        StockState $stockState,
        StockConfigurationInterface $stockConfiguration,
        ProductRepositoryInterface $productRepository,
        QtyCounterInterface $qtyCounter,
        StockRegistryStorage $stockRegistryStorage = null
    ) {
        parent::__construct($stockResource, $stockRegistryProvider, $stockState, $stockConfiguration,
            $productRepository, $qtyCounter, $stockRegistryStorage);

        $this->stockRegistryStorage = $stockRegistryStorage ?: ObjectManager::getInstance()
                                                                            ->get(StockRegistryStorage::class);
        $this->qtyCounter           = $qtyCounter;
    }

    /**
     * Subtract product qtys from stock.
     *
     * Return array of items that require full save.
     *
     * @param string[] $items
     * @param int      $websiteId
     *
     * @return StockItemInterface[]
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function registerProductsSale($items, $websiteId = null)
    {
        if ( ! $websiteId) {
            $websiteId = $this->stockConfiguration->getDefaultScopeId();
        }
        $this->getResource()->beginTransaction();
        $lockedItems   = $this->getResource()->lockProductsStock(array_keys($items), $websiteId);
        $fullSaveItems = $registeredItems = [];
        foreach ($lockedItems as $lockedItemRecord) {
            if (isset($lockedItemRecord['product_id'])) {
                $productId = $lockedItemRecord['product_id'];
                $this->stockRegistryStorage->removeStockItem($productId, $websiteId);

                /** @var StockItemInterface $stockItem */
                $orderedQty = $items[$productId];
                $stockItem  = $this->stockRegistryProvider->getStockItem($productId, $websiteId);
                $stockItem->setQty($lockedItemRecord['qty']); // update data from locked item
                $canSubtractQty = $stockItem->getItemId() && $this->canSubtractQty($stockItem);
                if ( ! $canSubtractQty || ! $this->stockConfiguration->isQty($lockedItemRecord['type_id'])) {
                    continue;
                }
                if ( ! $stockItem->hasAdminArea() && ! $this->stockState->checkQty($productId, $orderedQty,
                        $stockItem->getWebsiteId())) {
                    $this->getResource()->commit();
                    throw new LocalizedException(__('Not all of your products are available in the requested quantity.'));
                }
                if ($this->canSubtractQty($stockItem)) {
                    $stockItem->setQty($stockItem->getQty() - $orderedQty);
                }
                $registeredItems[$productId] = $orderedQty;

                if ( ! $this->stockState->verifyStock($productId,
                        $stockItem->getWebsiteId()) || $this->stockState->verifyNotification($productId,
                        $stockItem->getWebsiteId())) {
                    $fullSaveItems[] = $stockItem;
                }
            }
        }
        $this->qtyCounter->correctItemsQty($registeredItems, $websiteId, '-');
        $this->getResource()->commit();

        return $fullSaveItems;
    }

}