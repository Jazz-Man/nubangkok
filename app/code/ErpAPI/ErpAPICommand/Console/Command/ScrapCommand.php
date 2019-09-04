<?php

namespace ErpAPI\ErpAPICommand\Console\Command;

use Encomage\ErpIntegration\Helper\ApiClient;
use Encomage\ErpIntegration\Helper\CacheFile;
use Encomage\ErpIntegration\Helper\Data;
use Encomage\ErpIntegration\Helper\StringUtils;
use Encomage\ErpIntegration\Model\Api\ErpProduct;
use Exception;
use GuzzleHttp\Psr7\Response;
use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Model\Product;
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

    private $productsNamesValidate = [];
    /**
     * @var \Encomage\ErpIntegration\Helper\StringUtils
     */
    private $string;

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
        $this->productResource         = $productResource;
        $this->objectManager           = $objectManager;
        $this->categoryLinkManagement  = $categoryLinkManagement;
        $this->typeConfigurableProduct = $typeConfigurableProduct;
        $this->stockRegistryFactory    = $stockRegistryFactory;
        $this->linkManagementFactory   = $linkManagementFactory;

        parent::__construct();

        $this->storeManager = $storeManager;

        $this->apiClient = new ApiClient($scopeConfig);
        $this->cacheFile = new CacheFile($objectManager);

        $this->helper = $helper;

        $this->string = new StringUtils();

        $this->color_option_id = $this->productResource->getAttribute('color')->getId();
        $this->size_option_id  = $this->productResource->getAttribute('size')->getId();
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
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->cliOutput = $output;

        $this->cliOutput->writeln('Hello World!');

        $this->storeManager->setCurrentStore(Store::DEFAULT_STORE_ID);

        $CacheFile = $this->cacheFile->getCacheFile();

        if ($CacheFile) {
            $this->dataProccesing($CacheFile);
        } else {
            $query = [
                'Branchpricedisplay'    => 1,
                'CategoryDisplaySubCat' => 1,
                'Page'                  => 1,
            ];

            do {
                /** @var Response $response */
                $response = $this->apiClient->getData('GetProductList', $query);
            } while ($this->parseBody($response) && $query['Page']++);

            if ( ! empty($this->products_data)) {
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

                $size = ! empty($datum->getSize()) ? $this->helper->getSizeIdByName($datum->getSize()) : false;

                $poduct_props = [
                    'size'            => $size,
                    'weight' =>$datum->getGrossWeight(),
                    'salesPrice'      => $datum->getSalesPrice(),
                    'model'           => $datum->getPropModel(),
                    'stockStatus'     => $datum->getStockStatus(),
                    'unrestrictStock' => $datum->getUnrestrictStock(),
                    'isShoes'         => $datum->isShoes(),
                    'category_id'     => $category_id,
                ];

                if ($datum->getColor1()) {
                    $color1_name = "{$datum->getPropModel()} {$datum->getColor1()} {$datum->getSize()}";

                    if (empty($this->productsNamesValidate[$color1_name])){

                        $this->productsNamesValidate[$color1_name] = $datum->getBarCode();

                        $color = $this->helper->getColorIdByName($datum->getColor1());


                        $poduct_props = array_merge([
                            'name'            => $color1_name,
                            'sku'             => $datum->getBarCode(),
                            'color'           => $color
                        ], $poduct_props);

                        $this->prepareSimpleProduct($poduct_props);
                    }
                }


                if ($datum->getColor2()) {
                    $color2_name = "{$datum->getPropModel()} {$datum->getColor2()} {$datum->getSize()}";

                    if (empty($this->productsNamesValidate[$color2_name])){

                        $color2_sku = $this->helper->generateProductSku($datum->getBarCode(), [
                            'erp' => 'color',
                        ]);

                        $this->productsNamesValidate[$color2_name] = $color2_sku;

                        $color2 = $this->helper->getColorIdByName($datum->getColor2());

                        $poduct_props = array_merge([
                            'name'            => $color2_name,
                            'sku'             => $color2_sku,
                            'color'           => $color2
                        ], $poduct_props);

                        $this->prepareSimpleProduct($poduct_props);

                    }

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

        if ( ! empty($products_data)) {
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

                $categoty_id = ! empty($this->configurableProductCategory[$model]) ? $this->configurableProductCategory[$model] : null;

                $configurable_product = $this->helper->getProductBySky($config_sku);

                $is_new = true;
//                $is_new = null === $configurable_product->getId();

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

                        $this->cliOutput->writeln("Save Configurable Product: '{$configurable_product->getName()}'");
                    } catch (Exception $exception) {
                        dump($exception->getMessage());
                    }
                }


                if ( ! empty($categoty_id)) {
                    $this->cliOutput->writeln("Add Configurable Product categoty: '{$categoty_id}'");
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

                                $this->cliOutput->writeln("Save Configurable Relation: '{$configurable_product->getName()} => {$child}'");
                            } catch (Exception $exception) {
                                dump("Save Configurable Relation Exception: {$exception->getMessage()}");
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
                    dump($e->getMessage());
                }
            }
        }
    }

    /**
     * @param array $data
     */
    protected function prepareSimpleProduct(array $data)
    {
        list('name' => $name, 'sku' => $sku, 'color' => $color, 'size' => $size,'weight'=>$weight, 'salesPrice' => $salesPrice, 'model' => $model, 'stockStatus' => $stockStatus, 'unrestrictStock' => $unrestrictStock, 'isShoes' => $isShoes, 'category_id' => $category_id,) = $data;

        $product = $this->helper->getProductBySky($sku);

        $is_new = null === $product->getId();

        if ($is_new) {
            $product->setSku($sku)
                    ->setWebsiteIds([
                        Configuration::DEFAULT_WEBSITE_ID => Configuration::DEFAULT_WEBSITE_ID,
                    ])
                    ->setVisibility(Visibility::VISIBILITY_NOT_VISIBLE)
                    ->setStatus(Status::STATUS_ENABLED)
                    ->setStoreId(Store::DEFAULT_STORE_ID)
                    ->setTypeId(TypeAlias::TYPE_SIMPLE)
                    ->setAttributeSetId($product->getDefaultAttributeSetId());
        }

        $product->setPrice($salesPrice)
                ->setName($name)
            ->setWeight($weight)
                ->setQuantityAndStockStatus([
                StockItemAlias::IS_IN_STOCK => $stockStatus,
                StockItemAlias::QTY         => $unrestrictStock,
            ])->setAskAboutShoeSize($isShoes);

        if ( ! empty($color)) {
            $product->setColor($color);
        }

        if ( ! empty($size)) {
            $product->setSize($size);
        }

        $changed_data = [];

        $compare_array = [
            Product::NAME,
            Product::PRICE,
            Product::VISIBILITY,
            Product::WEIGHT,
            'color',
            'size',
            'quantity_and_stock_status',
        ];


        foreach ($compare_array as $item) {
            if ($product->dataHasChangedFor($item)) {
                $changed_data[] = $item;
            }
        }

        $old_assignedCategories = $product->getCategoryIds();

        if ((array)$category_id !== $old_assignedCategories) {
            $changed_data[] = 'category_ids';
        }

        $need_to_update_product = true;
//        $need_to_update_product = ! empty($changed_data);

        if ($need_to_update_product) {

            try {
                $this->productResource->save($product);

                $this->cliOutput->writeln("Save Product: '{$product->getName()}' '{$product->getSku()}'");


                if ( ! empty($category_id) && $is_new) {
                    $this->categoryLinkManagement->assignProductToCategories($sku, [$category_id]);
                }
            } catch (Exception $e) {
                dump($e->getMessage());
            }
        } else {
            $this->cliOutput->writeln("No Updates For Product: '{$product->getName()}'");
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

            $this->cliOutput->writeln("Save Product Stock Data: '{$product->getName()}'");


        } catch (Exception $exception) {
            dump($exception->getMessage());
        }

        $this->configurableProductCategory[$model] = $category_id;

        $has_parent = ! empty($this->typeConfigurableProduct->getParentIdsByChild($product->getId()));

        if ( ! $has_parent) {

            $this->configurableProductData[$model][] = $product->getSku();
        }

    }

}
