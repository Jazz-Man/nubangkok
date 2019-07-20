<?php

namespace ErpAPI\ErpAPICommand\Console\Command;

use ErpAPI\ErpAPICommand\Helper\ApiClient;
use ErpAPI\ErpAPICommand\Helper\CacheFile;
use ErpAPI\ErpAPICommand\Model\Erp\ErpProduct;
use Exception;
use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;
use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\CatalogInventory\Api\StockRegistryInterfaceFactory;
use Magento\CatalogInventory\Model\Configuration;
use Magento\ConfigurableProduct\Api\LinkManagementInterfaceFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as TypeConfigurableProduct;
use Magento\Eav\Api\AttributeOptionManagementInterface;
use Magento\Eav\Model\AttributeRepository;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Eav\Model\Entity\Attribute\Option;
use Magento\Eav\Model\Entity\Attribute\OptionFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\Store;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function GuzzleHttp\json_decode as json_decodeAlias;
use function iter\filter;
use function iter\map;

/**
 * Class HelloWorldCommand.
 */
class ScrapCommand extends Command
{
    /**
     * @var ScopeConfigInterface
     */
    private $_config;

    /**
     * @var string
     */
    private $_categories_codes;
    /**
     * @var string
     */
    private $_shoe_codes;
    /**
     * @var string
     */
    private $_bags_codes;
    /**
     * @var string
     */
    private $_color_code;
    /**
     * @var array
     */
    private $products_data = [[]];

    /**
     * @var CategoryResource
     */
    private $categoryResource;

    /**
     * @var ProductResource
     */
    private $productResource;

    /**
     * @var array
     */
    private $all_categories;
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    private $productFactory;
    /**
     * @var array
     */
    private $_attributesOptions;

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
     * @var OptionFactory
     */
    private $optionFactory;

    /**
     * @var AttributeOptionManagementInterface
     */
    private $attributeOptionManager;
    /**
     * @var AttributeRepository
     */
    private $attributeRepository;

    /**
     * @var int
     */
    private $size_attribute_id;
    /**
     * @var \ErpAPI\ErpAPICommand\Helper\ApiClient
     */
    private $apiClient;
    /**
     * @var \ErpAPI\ErpAPICommand\Helper\CacheFile
     */
    private $cacheFile;

    /**
     * ScrapCommand constructor.
     *
     * @param ScopeConfigInterface                                            $scopeConfig
     * @param CategoryResource                                                $categoryResource
     * @param ProductResource                                                 $productResource
     * @param ObjectManagerInterface                                          $objectManager
     * @param \Magento\Catalog\Model\ProductFactory                           $productFactory
     * @param Attribute                                                       $entityAttribute
     * @param CategoryLinkManagementInterface                                 $categoryLinkManagement
     * @param TypeConfigurableProduct                                         $typeConfigurableProduct
     * @param StockRegistryInterfaceFactory                                   $stockRegistryFactory
     * @param \Magento\ConfigurableProduct\Api\LinkManagementInterfaceFactory $linkManagementFactory
     * @param \Magento\Eav\Model\Entity\Attribute\OptionFactory               $optionFactory
     * @param AttributeOptionManagementInterface                              $attributeOptionManager
     * @param \Magento\Eav\Model\AttributeRepository                          $attributeRepository
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        CategoryResource $categoryResource,
        ProductResource $productResource,
        ObjectManagerInterface $objectManager,
        ProductFactory $productFactory,
        Attribute $entityAttribute,
        CategoryLinkManagementInterface $categoryLinkManagement,
        TypeConfigurableProduct $typeConfigurableProduct,
        StockRegistryInterfaceFactory $stockRegistryFactory,
        LinkManagementInterfaceFactory $linkManagementFactory,
        OptionFactory $optionFactory,
        AttributeOptionManagementInterface $attributeOptionManager,
        AttributeRepository $attributeRepository
    ) {
        $this->_config = $scopeConfig;
        $this->categoryResource = $categoryResource;
        $this->productResource = $productResource;
        $this->productFactory = $productFactory;
        $this->objectManager = $objectManager;
        $this->categoryLinkManagement = $categoryLinkManagement;
        $this->typeConfigurableProduct = $typeConfigurableProduct;
        $this->stockRegistryFactory = $stockRegistryFactory;
        $this->linkManagementFactory = $linkManagementFactory;
        $this->optionFactory = $optionFactory;
        $this->attributeOptionManager = $attributeOptionManager;
        $this->attributeRepository = $attributeRepository;

        parent::__construct();

        $this->apiClient = new ApiClient($scopeConfig);
        $this->cacheFile = new CacheFile($objectManager);

        $this->_attributesOptions['color'] = $entityAttribute->loadByCode('catalog_product', 'color')
                                                             ->getSource()
                                                             ->getAllOptions(false, true);

        $this->_attributesOptions['size'] = $entityAttribute->loadByCode('catalog_product', 'size')
                                                            ->getSource()
                                                            ->getAllOptions(false, true);
    }

    /**
     * @param mixed  $label
     * @param string $attrName
     *
     * @return array
     */
    public function getAttributeIdByLabel($label, $attrName)
    {
        foreach ($this->_attributesOptions[$attrName] as $option) {
            if ($option['label'] === $label) {
                return $option;
            }
        }

        return ['label' => '', 'value' => ''];
    }

    /**
     * @param ErpProduct                     $datum
     * @param \Magento\Catalog\Model\Product $_product
     * @param int                            $category_id
     *
     * @throws \Exception
     */
    public function buildConfigurableProduct($datum, $_product, $category_id)
    {
        $ConfigurableProduct = $this->typeConfigurableProduct->getParentIdsByChild($_product->getId());

        $configurable = [];

        if (!empty($ConfigurableProduct)) {
            if (!empty($datum->getConfigSku()) && !empty($datum->getConfigName())) {
                $configurable[$datum->getConfigSku()] = [
                    'name' => $datum->getConfigName(),
                    'category_ids' => $category_id,
                ];

                $configurable[$datum->getConfigSku()]['category_name'] = $datum->getCategoryName();
            }

            if ($_product->getId() && \array_key_exists($datum->getConfigSku(), $configurable)) {
                $configurable[$datum->getConfigSku()]['associate_ids'][$_product->getId()] = $_product->getId();
                $configurable[$datum->getConfigSku()]['skus'][] = $_product->getSku();
            }

            $configurable[$datum->getConfigSku()]['color'] = \array_key_exists('color',
                $configurable[$datum->getConfigSku()]) ? $configurable[$datum->getConfigSku()]['color'] : '';
            if (null === $configurable[$datum->getConfigSku()]['color']) {
                $configurable[$datum->getConfigSku()]['color'] = $_product->getColor() ?: null;
            }

            $configurable[$datum->getConfigSku()]['size'] = \array_key_exists('size',
                $configurable[$datum->getConfigSku()]) ? $configurable[$datum->getConfigSku()]['size'] : '';
            if (null === $configurable[$datum->getConfigSku()]['size']) {
                $configurable[$datum->getConfigSku()]['size'] = $_product->getSize() ?: null;
            }
        }

        if (!empty($configurable)) {
            foreach ($configurable as $sku => $settings) {
                $productId = $this->productResource->getIdBySku($sku);

                if (!empty($productId)) {
                    /** @var \Magento\Catalog\Model\Product $product */
                    $product = $this->productFactory->create();
                    $product->setSku($sku);
                    $product->setName($settings['name']);
                    $product->setTypeId(TypeConfigurableProduct::TYPE_CODE);
                    $product->setAttributeSetId($product->getDefaultAttributeSetId());
                    $product->setCategoryIds([$settings['category_ids']]);
                    $product->setColor(' ');
                    $product->setSize(' ');
                    $product->setStoreId(Store::DEFAULT_STORE_ID);
                    $product->setWebsiteIds([
                        Configuration::DEFAULT_WEBSITE_ID => Configuration::DEFAULT_WEBSITE_ID,
                    ]);

                    if ('S' === substr($sku, 1, 1)) {
                        $product->setAskAboutShoeSize(1);
                    }

                    $product->setUrlKey($this->getUrlKey($settings['name'], $settings['category_name']));

                    $attributes = [];

                    if ($settings['color']) {
                        $attributes[] = $this->productResource->getAttribute('color')->getId();
                    }
                    if ($settings['size']) {
                        $attributes[] = $this->productResource->getAttribute('size')->getId();
                    }

                    $product->getTypeInstance()->setUsedProductAttributeIds($attributes, $product);

                    $configurableAttributesData = $product->getTypeInstance()
                                                          ->getConfigurableAttributesAsArray($product);

                    $product->setCanSaveConfigurableAttributes(true);
                    $product->setConfigurableAttributesData($configurableAttributesData);
                    $configurableProductsData = [];
                    $product->setConfigurableProductsData($configurableProductsData);

                    $this->productResource->save($product);
                    $productId = $product->getId();

                    $this->categoryLinkManagement->assignProductToCategories($sku, [$settings['category_ids']]);
                }

                if ($settings['associate_ids']) {
                    foreach ($settings['skus'] as $childSku) {
                        /** @var \Magento\ConfigurableProduct\Api\LinkManagementInterface $linkManagement */
                        $linkManagement = $this->linkManagementFactory->create();
                        $linkManagement->addChild($sku, $childSku);
                    }
                    if ($productId) {
                        /** @var \Magento\CatalogInventory\Model\Stock\Item $stockItem */
                        $stockItem = $this->stockRegistryFactory->create()->getStockItem($productId);
                        if ($stockItem->getItemId()) {
                            $stockItem->setIsInStock(true);
                            $stockItem->setStockStatusChangedAutomaticallyFlag(true);
                            $stockItem->save();
                        }
                        unset($stockItem);
                    }
                }
            }
        }
    }

    /**
     * @param string $name
     * @param string $category_name
     *
     * @return string
     */
    private function getUrlKey($name, $category_name)
    {
        $urlKey = "{$this->sanitizeKey($category_name)}-{$this->sanitizeKey($name)}";

        return trim($urlKey);
    }

    /**
     * @param string $key
     *
     * @return string
     */
    private function sanitizeKey(string $key): string
    {
        return str_replace([' ', ','], '-', strtolower($key));
    }

    protected function configure()
    {
        $this->setName('erpapi:scrap')->setDescription('So much hello world.');

        $state = $this->objectManager->get(State::class);
        try {
            $state->setAreaCode('crontab');
        } catch (LocalizedException $e) {
        }

        $this->_color_code = $this->_config->getValue('erp_etoday_settings/color_settings/color_code');
        $this->_bags_codes = $this->_config->getValue('erp_etoday_settings/category_type_bags/bags_codes');
        $this->_shoe_codes = $this->_config->getValue('erp_etoday_settings/category_type_shoe/shoe_codes');
        $this->_categories_codes = $this->_config->getValue('erp_etoday_settings/categories/categories_codes');

        $this->setAllCategories();

        try {
            $this->size_attribute_id = $this->attributeRepository->get(Product::ENTITY, 'size')->getAttributeId();
        } catch (NoSuchEntityException $e) {
            $this->size_attribute_id = false;
        }

        parent::configure();
    }

    /**
     * @return \Magento\Eav\Api\Data\AttributeOptionInterface[]
     *
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     */
    private function getAllSizes()
    {
        return $this->attributeOptionManager->getItems(Product::ENTITY, $this->size_attribute_id);
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int|void|null
     *
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Hello World!');

        $CacheFile = $this->cacheFile->getCacheFile();

        if ($CacheFile) {
            $this->dataProccesing($CacheFile);
        } else {
            $last_point = 'GetProductList';

            $query = [
                'Branchpricedisplay' => 1,
                'CategoryDisplaySubCat' => 1,
                'Page' => 1,
            ];

            do {
                /** @var Response $response */
                $response = $this->apiClient->getData($last_point, $query);
            } while ($this->parseBody($response) && $query['Page']++);

            if (!empty($this->products_data)) {
                $this->products_data = array_merge(...$this->products_data);

                $this->cacheFile->saveCacheFile($this->products_data);

                $this->dataProccesing($this->products_data);
            }
        }

        dump(self::testMemory());
    }

    /**
     * @param array $products_data
     *
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function dataProccesing(array $products_data)
    {
        /** @var \Generator|ErpProduct[] $data */
        $data = map(static function ($value) {
            return new ErpProduct($value);
        }, $products_data);

        foreach ($data as $datum) {
            if ($datum->isValid()) {
                $productId = $this->productResource->getIdBySku($datum->getIcProductCode());
                $CategoryId = $this->getCategoryId($datum->getBarCode());

                /** @var \Magento\Catalog\Model\Product $product */
                $product = $this->productFactory->create();

                if ($productId) {
                    $product->load($productId);
                    $product->setPrice($datum->getSalesPrice());

                    $product->addData([
                        'quantity_and_stock_status' => [
                            'is_in_stock' => $datum->getStockStatus(),
                            'qty' => $datum->getUnrestrictStock(),
                        ],
                    ]);

                    if ($datum->getModelColor()) {
                        $colors = array_filter($this->getColorCode(), static function ($item) use ($datum) {
                            return \in_array($item['erp_color_code'], $datum->getModelColor());
                        });

                        if (!empty($colors)) {
                            $colors = array_column($colors, 'erp_color_value');

                            $product->setColor(reset($colors));
                        }
                    }

                    try {
                        $this->productResource->save($product);

                        dump("Save : '{$product->getName()}'");
                    } catch (Exception $e) {
                        dump($e->getMessage());
                    }
                } else {
                    $product->setSku($datum->getIcProductCode());
                    $product->setName($datum->getName());
                    $product->setAttributeSetId($product->getDefaultAttributeSetId());
                    $product->setVisibility(Visibility::VISIBILITY_NOT_VISIBLE);
                    $product->setTypeId('simple');
                    $product->setPrice($datum->getSalesPrice());
                    $product->setWeight(null);
                    $product->setStoreId(Store::DEFAULT_STORE_ID);
                    $product->setWebsiteIds([
                        Configuration::DEFAULT_WEBSITE_ID => Configuration::DEFAULT_WEBSITE_ID,
                    ]);
                    $product->addData([
                        'quantity_and_stock_status' => [
                            'is_in_stock' => $datum->getStockStatus(),
                            'qty' => $datum->getUnrestrictStock(),
                        ],
                    ]);

                    if ($datum->getModelColor()) {
                        $colors = array_filter($this->getColorCode(), static function ($item) use ($datum) {
                            return \in_array($item['erp_color_code'], $datum->getModelColor());
                        });

                        if (!empty($colors)) {
                            $colors = array_column($colors, 'erp_color_value');

                            $product->setColor(reset($colors));
                        }
                    }

                    if ($datum->getSize()) {
                        $size = $datum->getSize() / 10;

                        /** @var Option[] $option */
                        $option = array_filter($this->getAllSizes(), static function (Option $item) use ($size) {
                            return $item->getLabel() === (string) $size;
                        });

                        if (!empty($option)) {
                            $option = reset($option);

                            $product->setSize($option->getLabel());
                        } elseif ($option = $this->createSizeOption($size)) {
                            $product->setSize($option->getLabel());
                        }
                    }

                    $product->setUrlKey($datum->getUrlKey());

                    try {
                        $this->productResource->save($product);

                        if (!empty($CategoryId)) {
                            $this->categoryLinkManagement->assignProductToCategories($datum->getIcProductCode(),
                                [$CategoryId]);
                        }

                        $this->buildConfigurableProduct($datum, $product, $CategoryId);
                    } catch (Exception $e) {
                        dump($e->getMessage());
                    }
                }
            }
        }
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return bool
     */
    protected function parseBody(ResponseInterface $response)
    {
        $products_data = $this->apiClient->parseBody($response);

        if (!empty($products_data)) {
            $this->products_data[] = $products_data;

            return true;
        }

        return false;
    }

    /**
     * @param int $precision
     *
     * @return string
     */
    protected static function testMemory($precision = 2)
    {
        $bytes = memory_get_peak_usage();
        $units = ['b', 'kb', 'mb', 'gb', 'tb'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, \count($units) - 1);

        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision).' '.$units[$pow];
    }

    /**
     * @param string $bar_code
     *
     * @return string
     */
    private function getCategoryId(string $bar_code)
    {
        $category = '';
        $subCategory = '';

        $erpCategoryCode = substr($bar_code, 0, 2);
        $typeProduct = substr($bar_code, 1, 1);
        $erpSubCategoryCode = substr($bar_code, 2, 1);

        if (null !== $erpCategoryCode) {
            $cat = filter(static function ($item) use ($erpCategoryCode) {
                return $item['erp_category_code'] === $erpCategoryCode;
            }, $this->getCategoriesCodes());

            if ($cat->valid()) {
                $category = $cat->current()['category_path'];
            }
        }

        if (null !== $typeProduct) {
            if ('S' === $typeProduct) {
                $cat = filter(static function ($item) use ($erpSubCategoryCode) {
                    return $item['erp_shoe_code'] === $erpSubCategoryCode;
                }, $this->getShoeCodes());

                if ($cat->valid()) {
                    $subCategory = $cat->current()['shoe_category_value'];
                }
            } elseif ('B' === $typeProduct) {
                $cat = filter(static function ($item) use ($erpSubCategoryCode) {
                    return $item['erp_bags_code'] === $erpSubCategoryCode;
                }, $this->getBagsCodes());

                if ($cat->valid()) {
                    $subCategory = $cat->current()['bags_category_value'];
                }
            }
        }

        if (!empty($category)) {
            if (!empty($subCategory)) {
                $category .= "/{$subCategory}";
            }

            $entity_id = filter(static function ($item) use ($category) {
                if ($item['value'] === $category) {
                    return true;
                }

                $arrCategory = explode('/', $category);
                array_pop($arrCategory);

                $category = implode('/', $arrCategory);

                if ($item['value'] === $category) {
                    return true;
                }

                if ('default-category' === $item['value']) {
                    return true;
                }

                return false;
            }, $this->getAllCategories());

            if ($entity_id->valid()) {
                return $entity_id->current()['entity_id'];
            }
        }

        return false;
    }

    /**
     * @return array|mixed
     */
    private function getCategoriesCodes()
    {
        try {
            $categories_data = json_decodeAlias($this->_categories_codes, true);
        } catch (InvalidArgumentException $e) {
            $categories_data = [];
        }

        return $categories_data;
    }

    /**
     * @return array|mixed
     */
    private function getShoeCodes()
    {
        try {
            $shoe_codes_data = json_decodeAlias($this->_shoe_codes, true);
        } catch (InvalidArgumentException $e) {
            $shoe_codes_data = [];
        }

        return $shoe_codes_data;
    }

    /**
     * @return array|mixed
     */
    private function getBagsCodes()
    {
        try {
            $bags_codes_data = json_decodeAlias($this->_bags_codes, true);
        } catch (InvalidArgumentException $e) {
            $bags_codes_data = [];
        }

        return $bags_codes_data;
    }

    /**
     * @return array
     */
    private function getAllCategories()
    {
        if (empty($this->all_categories)) {
            $this->setAllCategories();
        }

        return $this->all_categories;
    }

    private function setAllCategories()
    {
        $category_table = $this->categoryResource->getTable('catalog_category_entity_varchar');

        $select = $this->categoryResource->getConnection()
                                         ->select()
                                         ->from($category_table)
                                         ->where('value IS NOT NULL')
                                         ->where('store_id = ?', Store::DEFAULT_STORE_ID);

        $this->all_categories = $this->categoryResource->getConnection()->fetchAll($select);
    }

    /**
     * @return array
     */
    private function getColorCode()
    {
        $color_code = json_decodeAlias($this->_color_code, true);
        $color_code = array_values($color_code);

        return $color_code;
    }

    /**
     * @param int|string $size
     *
     * @return bool|\Magento\Eav\Model\Entity\Attribute\Option
     */
    private function createSizeOption($size)
    {
        /** @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection $attributeOptionCollection */
        $attributeOptionCollection = $this->objectManager->create(Collection::class);

        $optionDataArray = $attributeOptionCollection->setAttributeFilter($this->size_attribute_id)
                                                     ->setStoreFilter(Store::DEFAULT_STORE_ID)
                                                     ->load()
                                                     ->getData();

        $optionData = array_filter($optionDataArray, static function ($item) use ($size) {
            return $item['value'] === (string) $size;
        });

        if (empty($optionData)) {
            /** @var Option $option */
            $option = $this->optionFactory->create();
            $option->setLabel((string) $size);

            try {
                $this->attributeOptionManager->add(Product::ENTITY, $this->size_attribute_id, $option);

                return $option;
            } catch (InputException $e) {
                dump($e->getMessage());
            } catch (StateException $e) {
                dump($e->getMessage());
            }

            return false;
        }

        return false;
    }
}
