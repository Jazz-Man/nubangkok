<?php

namespace ErpAPI\ErpAPICommand\Console\Command;

use Encomage\ErpIntegration\Helper\ApiClient;
use Encomage\ErpIntegration\Helper\CacheFile;
use Encomage\ErpIntegration\Helper\Data;
use Exception;
use GuzzleHttp\Psr7\Response;
use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
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
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class HelloWorldCommand.
 */
class ScrapCommand extends Command
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
     * @var ObjectManagerInterface
     */
    private $objectManager;

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
     * @var \Encomage\ErpIntegration\Helper\CacheFile
     */
    private $cacheFile;
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
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    private $cliOutput;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var array
     */
    private $configurableProductData = [];

    /**
     * @var array
     */
    private $configurableProductCategory = [];

    private $askAboutShoeSize = [];
    /**
     * @var array
     */
    private $productNames = [];
    /**
     * @var \Symfony\Component\Console\Helper\Table
     */
    private $cliTable;

    /**
     * ScrapCommand constructor.
     *
     * @param ScopeConfigInterface                                            $scopeConfig
     * @param ProductResource                                                 $productResource
     * @param ObjectManagerInterface                                          $objectManager
     * @param CategoryLinkManagementInterface                                 $categoryLinkManagement
     * @param TypeConfigurableProduct                                         $typeConfigurableProduct
     * @param StockRegistryInterfaceFactory                                   $stockRegistryFactory
     * @param \Magento\ConfigurableProduct\Api\LinkManagementInterfaceFactory $linkManagementFactory
     * @param \Magento\Store\Model\StoreManagerInterface                      $storeManager
     * @param \Encomage\ErpIntegration\Helper\Data                            $helper
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ProductResource $productResource,
        ObjectManagerInterface $objectManager,
        CategoryLinkManagementInterface $categoryLinkManagement,
        TypeConfigurableProduct $typeConfigurableProduct,
        StockRegistryInterfaceFactory $stockRegistryFactory,
        LinkManagementInterfaceFactory $linkManagementFactory,
        StoreManagerInterface $storeManager,
        Data $helper
    ) {
        $this->productResource = $productResource;
        $this->objectManager = $objectManager;
        $this->categoryLinkManagement = $categoryLinkManagement;
        $this->typeConfigurableProduct = $typeConfigurableProduct;
        $this->stockRegistryFactory = $stockRegistryFactory;
        $this->linkManagementFactory = $linkManagementFactory;

        parent::__construct();

        $this->storeManager = $storeManager;

        $this->apiClient = new ApiClient($scopeConfig);
        $this->cacheFile = new CacheFile($objectManager);

        $this->helper = $helper;

        $this->color_option_id = $this->productResource->getAttribute('color')->getId();
        $this->size_option_id = $this->productResource->getAttribute('size')->getId();
    }

    protected function configure()
    {
        $this->setName('erpapi:scrap')->setDescription('So much hello world.');

        $state = $this->objectManager->get(State::class);

        try {
            $state->getAreaCode();
        } catch (LocalizedException $e) {
            $state->setAreaCode('adminhtml');
        }

        parent::configure();
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface                 $input
     * @param \Symfony\Component\Console\Output\ConsoleOutput|OutputInterface $output
     *
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->cliOutput = $output;

        $this->cliOutput->writeln('Hello World!');

        $this->cliTable = new Table($output);

        $this->storeManager->setCurrentStore(Store::DEFAULT_STORE_ID);

        $CacheFile = $this->cacheFile->getCacheFile();

        if ($CacheFile) {
            $this->dataProccesing($CacheFile);
        } else {
            $query = [
                'Branchpricedisplay' => 1,
                'CategoryDisplaySubCat' => 1,
                'Page' => 1,
            ];

            do {
                /** @var Response $response */
                $response = $this->apiClient->getData('GetProductList', $query);
            } while ($this->parseBody($response) && $query['Page']++);

            if (!empty($this->products_data)) {
                $this->products_data = array_merge(...$this->products_data);

                $this->cacheFile->saveCacheFile($this->products_data);

                $this->dataProccesing($this->products_data);
            }
        }

        dump($this->helper->testMemory());
    }

    /**
     * @param array $products_data
     */
    protected function dataProccesing(array $products_data): void
    {
        $data = $this->helper->getErpProductsObjects($products_data);

        foreach ($data as $datum) {
            if ($datum->isValid()) {
                $category_id = $this->getCategoryId($datum);

                $size = !empty($datum->getSize()) ? $this->helper->getSizeIdByName($datum->getSize()) : false;

                $poduct_props = [
                    'size' => $size,
                    'weight' => $datum->getGrossWeight(),
                    'salesPrice' => $datum->getSalesPrice(),
                    'model' => $datum->getModel(),
                    'stockStatus' => $datum->getStockStatus(),
                    'unrestrictStock' => $datum->getUnrestrictStock(),
                    'isShoes' => $datum->isShoes(),
                    'category_id' => $category_id,
                ];

                if ($datum->getColor1()) {
                    $color1_name = "{$datum->getModel()} {$datum->getColor1()} {$datum->getSize()}";

//                    if (empty($this->productNames[$color1_name])) {
//                        $this->productNames[$color1_name] = $color1_name;

                        $color = $this->helper->getColorIdByName($datum->getColor1());

                        $poduct_props_2 = array_merge([
                            'name' => $color1_name,
                            'sku' => $datum->getBarCode(),
                            'color' => $color,
                        ], $poduct_props);

                        $this->prepareSimpleProduct($poduct_props_2);
//                    }
                }

                if ($datum->getColor2()) {
                    $color2_name = "{$datum->getModel()} {$datum->getColor2()} {$datum->getSize()}";

//                    if (empty($this->productNames[$color2_name])) {
//                        $this->productNames[$color2_name] = $color2_name;

                        $color2_sku = $this->helper->generateProductSku($datum->getBarCode(), [
                            'erp' => 'color',
                        ]);

                        $color2 = $this->helper->getColorIdByName($datum->getColor2());

                        $poduct_props_2 = array_merge([
                            'name' => $color2_name,
                            'sku' => $color2_sku,
                            'color' => $color2,
                        ], $poduct_props);

                        $this->prepareSimpleProduct($poduct_props_2);
//                    }
                }
            }
        }

        $this->buildConfigurable();
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return bool
     */
    protected function parseBody(ResponseInterface $response): bool
    {
        $products_data = $this->apiClient->parseBody($response);

        if (!empty($products_data)) {
            $this->products_data[] = $products_data;

            return true;
        }

        return false;
    }

    /**
     * @param \Encomage\ErpIntegration\Model\Api\ErpProduct $erp_product
     *
     * @return bool|int
     */
    private function getCategoryId($erp_product)
    {
        $category_path = [
            $erp_product->getRootCategoryName(),
        ];

        if (!empty($erp_product->getSubCategoryName())) {
            $category_path[] = $erp_product->getSubCategoryName();
        }

        if (!empty($erp_product->getFormat()) && $erp_product->getFormat() !== $erp_product->getSubCategoryName()) {
            $category_path[] = $this->helper->sanitizeKey($erp_product->getFormat());
        }

        $category = implode('/', $category_path);

        $category = strtolower($category);

        return $this->helper->getCategoryByPath($category);
    }

    protected function buildConfigurable(): void
    {
        if (!empty($this->configurableProductData)) {
            foreach ($this->configurableProductData as $model => $childrens) {
                $config_sku = $this->helper->generateProductSku($model);

                $categoty_id = !empty($this->configurableProductCategory[$model]) ? $this->configurableProductCategory[$model] : null;

                $product = $this->_prepareProduct($config_sku, $model, TypeConfigurableProduct::TYPE_CODE,
                    Visibility::VISIBILITY_BOTH);

                if (!empty($this->askAboutShoeSize[$model])) {
                    $product->setAskAboutShoeSize($this->askAboutShoeSize[$model]);
                }

                $this->typeConfigurableProduct->setUsedProductAttributeIds([
                    $this->color_option_id,
                    $this->size_option_id,
                ], $product);

                $configurableAttributesData = $this->typeConfigurableProduct->getConfigurableAttributesAsArray($product);

                $product->setCanSaveConfigurableAttributes(true)
                        ->setConfigurableAttributesData($configurableAttributesData)
                        ->setConfigurableProductsData([]);

                $this->_saveProduct($product);

                if (!empty($categoty_id)) {
                    $this->categoryLinkManagement->assignProductToCategories($config_sku, [$categoty_id]);
                }

                if (!empty($childrens)) {
                    foreach ($childrens as $child) {
                        $child_product = $this->helper->getProductBySky($child);

                        $has_parent = !empty($this->typeConfigurableProduct->getParentIdsByChild($child_product->getId()));

                        if (!$has_parent) {
                            /** @var LinkManagement $linkManagement */
                            $linkManagement = $this->linkManagementFactory->create();

                            try {
                                $linkManagement->addChild($product->getSku(), $child_product->getSku());

                                $this->printProductException('Save Configurable Relation', $product, 'info', null,
                                    $child);
                            } catch (Exception $e) {
                                $this->printProductException('Save Relation Exception', $product, 'error', $e, $child);
                            }
                        }
                    }
                }

                try {
                    /** @var \Magento\CatalogInventory\Model\Stock\Item $stockItem */
                    $stockItem = $this->stockRegistryFactory->create()->getStockItem($product->getId());
                    $stockItem->setProduct($product)
                              ->setIsInStock(true)
                              ->setStoreId(Store::DEFAULT_STORE_ID)
                              ->setStockStatusChangedAutomaticallyFlag(true);
                    $stockItem->save();
                } catch (Exception $e) {
                    $this->printProductException('Save Product Stock Data', $product, 'error', $e);
                }
            }
        }
    }

    /**
     * @param array $data
     */
    protected function prepareSimpleProduct(array $data)
    {
        [
            'name' => $name,
            'sku' => $sku,
            'color' => $color,
            'size' => $size,
            'weight' => $weight,
            'salesPrice' => $salesPrice,
            'model' => $model,
            'stockStatus' => $stockStatus,
            'unrestrictStock' => $unrestrictStock,
            'isShoes' => $isShoes,
            'category_id' => $category_id,
        ] = $data;

        $product = $this->_prepareProduct($sku, $name);

        $product->setPrice($salesPrice)->setWeight($weight)->setQuantityAndStockStatus([
            StockItemAlias::IS_IN_STOCK => $stockStatus,
            StockItemAlias::QTY => $unrestrictStock,
        ])->setColor($color)->setSize($size);

        $this->askAboutShoeSize[$model] = $isShoes;

        $this->_saveProduct($product);

        if (!empty($category_id)) {
            $this->categoryLinkManagement->assignProductToCategories($sku, [$category_id]);
        }

        try {
            /** @var \Magento\CatalogInventory\Model\Stock\Item $stockItem */
            $stockItem = $this->stockRegistryFactory->create()->getStockItem($product->getId());
            $stockItem->setProduct($product)
                      ->setStoreId(Store::DEFAULT_STORE_ID)
                      ->setIsInStock($stockStatus)
                      ->setQty($unrestrictStock)
                      ->setIsQtyDecimal(false);

            $stockItem->save();

            $this->printProductException('Save Product Stock Data', $product);
        } catch (Exception $e) {
            $this->printProductException('Save Product Stock Data', $product, 'error', $e);
        }

        $this->configurableProductCategory[$model] = $category_id;

        $has_parent = !empty($this->typeConfigurableProduct->getParentIdsByChild($product->getId()));

        if (!$has_parent) {
            $this->configurableProductData[$model][] = $product->getSku();
        }
    }

    /**
     * @param string $sku
     * @param string $name
     * @param string $type_id
     * @param int    $visibility
     *
     * @return \Magento\Catalog\Api\Data\ProductInterface|\Magento\Catalog\Model\Product
     */
    private function _prepareProduct(
        string $sku,
        string $name,
        $type_id = TypeAlias::TYPE_SIMPLE,
        $visibility = Visibility::VISIBILITY_NOT_VISIBLE
    ) {
        $product = $this->helper->getProductBySky($sku);

        $is_new = null === $product->getId();

        if ($is_new) {
            $product->setSku($sku);
        }

        $product->setName($name)
                ->setWebsiteIds([
                    Configuration::DEFAULT_WEBSITE_ID => Configuration::DEFAULT_WEBSITE_ID,
                ])
                ->setVisibility($visibility)
                ->setStatus(Status::STATUS_ENABLED)
                ->setStoreId(Store::DEFAULT_STORE_ID)
                ->setTypeId($type_id)
                ->setAttributeSetId($product->getDefaultAttributeSetId());

        return $product;
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface|\Magento\Catalog\Model\Product $product
     */
    private function _saveProduct($product): void
    {
        try {
            $this->productResource->save($product);

            $this->printProductException("Save {$product->getTypeId()} product", $product);
        } catch (Exception $e) {
            $this->printProductException("Save {$product->getTypeId()} product", $product, 'error', $e);
        }
    }

    /**
     * @param string                                                                    $sction
     * @param \Magento\Catalog\Api\Data\ProductInterface|\Magento\Catalog\Model\Product $product
     * @param string                                                                    $color_style
     * @param \Exception                                                                $exception
     * @param string                                                                    $child_sku
     */
    private function printProductException(
        string $sction,
        $product,
        string $color_style = 'info',
        Exception $exception = null,
        $child_sku = ''
    ) {
        $tableStyle = new TableStyle();

        $tableStyle->setCellHeaderFormat("<{$color_style}>%s</{$color_style}>");
        $tableStyle->setDefaultCrossingChar("<{$color_style}>+</{$color_style}>");
        $tableStyle->setVerticalBorderChars("<{$color_style}>|</{$color_style}>");
        $tableStyle->setHorizontalBorderChars("<{$color_style}>-</{$color_style}>");

        $data = [
            'Action' => $sction,
            'Product SKU' => $product->getSku(),
            'Product Name' => $product->getName(),
            'Product Type' => $product->getTypeId(),
            'Store ID' => $product->getStoreId(),
        ];

        if (!empty($child_sku)) {
            $data['Child SKU'] = $child_sku;
        }

        if (null !== $exception) {
            $data['Exception'] = $exception->getMessage();
        }

        $headers = array_keys($data);

        $this->cliTable->setStyle($tableStyle)->setHeaders($headers)->setRows([$data]);
        $this->cliTable->render();
    }
}
