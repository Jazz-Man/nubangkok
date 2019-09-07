<?php

namespace Encomage\ErpIntegration\Cron;

use Encomage\ErpIntegration\Helper\ApiClient;
use Encomage\ErpIntegration\Helper\Data;
use Exception;
use GuzzleHttp\Psr7\Response;
use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Type as TypeAlias;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\CatalogInventory\Api\StockRegistryInterfaceFactory;
use Magento\CatalogInventory\Model\Configuration;
use Magento\CatalogInventory\Model\Stock\Item as StockItemAlias;
use Magento\ConfigurableProduct\Api\LinkManagementInterfaceFactory;
use Magento\ConfigurableProduct\Model\LinkManagement;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as TypeConfigurableProduct;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\Store;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;


/**
 * Class ImportProducts
 *
 * @package Encomage\ErpIntegration\Cron
 */
class ImportProducts
{

    /**
     * @var array
     */
    private $products_data = [[]];

    /**
     * @var ProductResource
     */
    private $productResource;

    /**
     * @var CategoryLinkManagementInterface
     */
    private $categoryLinkManagement;
    /**
     * @var TypeConfigurableProduct
     */
    private $typeConfigurableProduct;
    /**
     * @var StockRegistryInterfaceFactory
     */
    private $stockRegistryFactory;

    /**
     * @var LinkManagementInterfaceFactory
     */
    private $linkManagementFactory;

    /**
     * @var \Encomage\ErpIntegration\Helper\ApiClient
     */
    private $apiClient;

    /**
     * @var \Encomage\ErpIntegration\Helper\Data
     */
    private $helper;

    /**
     * @var int
     */
    private $color_option_id;
    /**
     * @var int
     */
    private $size_option_id;
    /**
     * @var array
     */
    private $configurableProductData = [];

    private $configurableProductCategity = [];
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * ImportProducts constructor.
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface              $scopeConfig
     * @param \Magento\Catalog\Model\ResourceModel\Product                    $productResource
     * @param \Magento\Catalog\Api\CategoryLinkManagementInterface            $categoryLinkManagement
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable    $typeConfigurableProduct
     * @param \Magento\CatalogInventory\Api\StockRegistryInterfaceFactory     $stockRegistryFactory
     * @param \Magento\ConfigurableProduct\Api\LinkManagementInterfaceFactory $linkManagementFactory
     * @param \Psr\Log\LoggerInterface                                        $logger
     * @param \Encomage\ErpIntegration\Helper\Data                            $helper
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ProductResource $productResource,
        CategoryLinkManagementInterface $categoryLinkManagement,
        TypeConfigurableProduct $typeConfigurableProduct,
        StockRegistryInterfaceFactory $stockRegistryFactory,
        LinkManagementInterfaceFactory $linkManagementFactory,
        LoggerInterface $logger,
        Data $helper
    ) {

        $this->productResource         = $productResource;
        $this->categoryLinkManagement  = $categoryLinkManagement;
        $this->typeConfigurableProduct = $typeConfigurableProduct;
        $this->stockRegistryFactory    = $stockRegistryFactory;
        $this->linkManagementFactory   = $linkManagementFactory;


        $this->apiClient = new ApiClient($scopeConfig);


        $this->helper = $helper;

        $this->color_option_id = $this->productResource->getAttribute('color')->getId();
        $this->size_option_id  = $this->productResource->getAttribute('size')->getId();
        $this->logger          = $logger;
    }

    /**
     * Write to system.log
     *
     * @return void
     */
    public function execute(): void
    {
        $this->logger->info('Start ERP Cron Import');


        $last_point = 'GetProductList';

        $query = [
            'Branchpricedisplay'    => 1,
            'CategoryDisplaySubCat' => 1,
            'Page'                  => 1,
        ];

        do {
            /** @var Response $response */
            $response = $this->apiClient->getData($last_point, $query);
        } while ($this->parseBody($response) && $query['Page']++);

        if ( ! empty($this->products_data)) {
            $this->products_data = array_merge(...$this->products_data);

            $this->dataProccesing($this->products_data);
        }

        $this->logger->info('Finish ERP Cron Import');

    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return bool
     */
    private function parseBody(ResponseInterface $response): bool
    {
        $products_data = $this->apiClient->parseBody($response);

        if ( ! empty($products_data)) {
            $this->products_data[] = $products_data;

            return true;
        }

        return false;
    }

    /**
     * @param array $products_data
     */
    protected function dataProccesing(array $products_data): void
    {
        $data = $this->helper->getErpProductsObjects($products_data);

        foreach ($data as $datum) {
            if ($datum->isValid()) {
                $product = $this->helper->getProductBySky($datum->getBarCode());
                $is_new  = null === $product->getId();

                $category_id = $this->getCategoryId($datum);
                $color       = ! empty($datum->getColor1()) ? $this->helper->getColorIdByName($datum->getColor1()) : false;

                $size = ! empty($datum->getSize()) ? $this->helper->getSizeIdByName($datum->getSize()) : false;

                if ($is_new) {
                    $product->setSku($datum->getBarCode())
                            ->setWebsiteIds([
                                Configuration::DEFAULT_WEBSITE_ID => Configuration::DEFAULT_WEBSITE_ID,
                            ])
                            ->setVisibility(Visibility::VISIBILITY_NOT_VISIBLE)
                            ->setStatus(Status::STATUS_ENABLED)
                            ->setStoreId(Store::DEFAULT_STORE_ID)
                            ->setTypeId(TypeAlias::TYPE_SIMPLE)
                            ->setAttributeSetId($product->getDefaultAttributeSetId());
                }

                $product->setPrice($datum->getSalesPrice())->setName($datum->getName())->setQuantityAndStockStatus([
                        StockItemAlias::IS_IN_STOCK => $datum->getStockStatus(),
                        StockItemAlias::QTY         => $datum->getUnrestrictStock(),
                    ]);

                if ( ! empty($color)) {
                    $product->setColor($color);
                }

                if ( ! empty($size)) {
                    $product->setSize($size);
                }

                $changed_data = [];

                $compare_array = [Product::NAME, Product::PRICE, 'color', 'size', 'quantity_and_stock_status'];


                foreach ($compare_array as $item) {
                    if ($product->dataHasChangedFor($item)) {
                        $changed_data[] = $item;
                    }
                }

                $old_assignedCategories = $product->getCategoryIds();

                if ((array)$category_id !== $old_assignedCategories) {
                    $changed_data[] = 'category_ids';
                }

                $need_to_update_product = ! empty($changed_data);

                if ($need_to_update_product) {

                    try {
                        $this->productResource->save($product);


                        if ( ! empty($category_id) && $is_new) {
                            $this->categoryLinkManagement->assignProductToCategories($datum->getBarCode(),
                                [$category_id]);
                        }
                    } catch (Exception $e) {
                        $this->logger->error($e->getMessage());
                    }
                }


                try {

                    /** @var \Magento\CatalogInventory\Model\Stock\Item $stockItem */
                    $stockItem = $this->stockRegistryFactory->create()->getStockItem($product->getId());
                    $stockItem->setProduct($product)
                              ->setStoreId(Store::DEFAULT_STORE_ID)
                              ->setIsInStock($datum->getStockStatus())
                              ->setQty($datum->getUnrestrictStock())
                              ->setIsQtyDecimal(false);


                    $stockItem->save();

                } catch (Exception $exception) {
                    $this->logger->error($exception->getMessage());
                }


                $this->configurableProductCategity[$datum->getModel()] = $category_id;

                $has_parent = ! empty($this->typeConfigurableProduct->getParentIdsByChild($product->getId()));

                if ( ! $has_parent) {
                    $this->configurableProductData[$datum->getModel()][] = $product->getSku();
                }
            }
        }

        $this->buildConfigurable();
    }

    /**
     * @param \Encomage\ErpIntegration\Model\Api\ErpProduct $erp_product
     *
     * @return bool|int
     */
    private function getCategoryId($erp_product)
    {
        $_category_path = [
            $erp_product->getRootCategoryName(),
        ];

        if ( ! empty($erp_product->getSubCategoryName())) {
            $_category_path[] = $erp_product->getSubCategoryName();
        }

        if ( ! empty($erp_product->getFormat())) {
            $_category_path[] = $this->helper->sanitizeKey($erp_product->getFormat());
        }

        $_category = implode('/', $_category_path);

        $_category = strtolower($_category);

        return $this->helper->getCategoryByPath($_category);
    }

    protected function buildConfigurable(): void
    {
        if ( ! empty($this->configurableProductData)) {
            foreach ($this->configurableProductData as $model => $children) {
                $config_sku = $this->helper->generateProductSku($model);

                $categoty_id = ! empty($this->configurableProductCategity[$model]) ? $this->configurableProductCategity[$model] : null;

                $configurable_product = $this->helper->getProductBySky($config_sku);

                $is_new = null === $configurable_product->getId();

                if ($is_new) {
                    $configurable_product->setSku($config_sku)
                                         ->setName($model)
                                         ->setTypeId(TypeConfigurableProduct::TYPE_CODE)
                                         ->setAttributeSetId($configurable_product->getDefaultAttributeSetId())
                                         ->setVisibility(Visibility::VISIBILITY_BOTH)
                                         ->setStatus(Status::STATUS_ENABLED)
                                         ->setStoreId(Store::DEFAULT_STORE_ID)
                                         ->setWebsiteIds([
                                             Configuration::DEFAULT_WEBSITE_ID => Configuration::DEFAULT_WEBSITE_ID,
                                         ]);

                    $this->typeConfigurableProduct->setUsedProductAttributes($configurable_product, [
                        $this->color_option_id,
                        $this->size_option_id,
                    ]);

                    $configurableAttributesData = $this->typeConfigurableProduct->getConfigurableAttributesAsArray($configurable_product);

                    $configurable_product->setCanSaveConfigurableAttributes(true)
                                         ->setConfigurableAttributesData($configurableAttributesData)
                                         ->setConfigurableProductsData([]);

                    try {
                        $this->productResource->save($configurable_product);

                    } catch (Exception $exception) {
                        $this->logger->error($exception->getMessage());
                    }
                }

                if ( ! empty($categoty_id)) {
                    $this->categoryLinkManagement->assignProductToCategories($config_sku, [$categoty_id]);
                }

                if ( ! empty($children)) {
                    foreach ($children as $child) {

                        $child_product = $this->helper->getProductBySky($child);

                        $has_parent = ! empty($this->typeConfigurableProduct->getParentIdsByChild($child_product->getId()));

                        if ( ! $has_parent) {
                            /** @var LinkManagement $linkManagement */
                            $linkManagement = $this->linkManagementFactory->create();

                            try {
                                $linkManagement->addChild($config_sku, $child);

                            } catch (Exception $exception) {
                                $this->logger->error("Save Configurable Relation Exception: {$exception->getMessage()}");
                            }
                        }
                    }

                }

                try {
                    /** @var \Magento\CatalogInventory\Model\Stock\Item $stockItem */
                    $stockItem = $this->stockRegistryFactory->create()->getStockItem($configurable_product->getId());
                    $stockItem->setProduct($configurable_product);
                    $stockItem->setIsInStock(true);
                    $stockItem->setStockStatusChangedAutomaticallyFlag(true);
                    $stockItem->save();


                } catch (Exception $e) {
                    $this->logger->error($e->getMessage());
                }
            }
        }
    }

}