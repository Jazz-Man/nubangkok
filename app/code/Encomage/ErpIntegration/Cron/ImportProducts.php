<?php
namespace Encomage\ErpIntegration\Cron;

use Encomage\ErpIntegration\Helper\ApiClient;
use Encomage\ErpIntegration\Helper\Data;
use Encomage\ErpIntegration\Model\Api\ErpProduct;
use Exception;
use GuzzleHttp\Psr7\Response;
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
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\Store;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use function iter\filter;


/**
 * Class ImportProducts
 *
 * @package Encomage\ErpIntegration\Cron
 */
class ImportProducts
{

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category
     */
    private $categoryResource;
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    private $productResource;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    private $productFactory;

    /**
     * @var \Magento\Catalog\Api\CategoryLinkManagementInterface
     */
    private $categoryLinkManagement;
    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    private $typeConfigurableProduct;
    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterfaceFactory
     */
    private $stockRegistryFactory;
    /**
     * @var \Magento\ConfigurableProduct\Api\LinkManagementInterfaceFactory
     */
    private $linkManagementFactory;
    /**
     * @var \Magento\Eav\Model\Entity\Attribute\OptionFactory
     */
    private $optionFactory;
    /**
     * @var \Magento\Eav\Api\AttributeOptionManagementInterface
     */
    private $attributeOptionManager;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    /**
     * @var \Encomage\ErpIntegration\Helper\ApiClient
     */
    private $apiClient;

    /**
     * @var array
     */
    private $_attributesOptions;
    /**
     * @var int|bool
     */
    private $size_attribute_id;
    /**
     * @var array
     */
    private $all_categories;
    /**
     * @var \Encomage\ErpIntegration\Helper\Data
     */
    private $helper;
    /**
     * @var array
     */
    private $products_data = [[]];

    /**
     * ImportProducts constructor.
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface              $scopeConfig
     * @param \Magento\Catalog\Model\ResourceModel\Category                   $categoryResource
     * @param \Magento\Catalog\Model\ResourceModel\Product                    $productResource
     * @param \Magento\Framework\ObjectManagerInterface                       $objectManager
     * @param \Magento\Catalog\Model\ProductFactory                           $productFactory
     * @param \Magento\Eav\Model\Entity\Attribute                             $entityAttribute
     * @param \Magento\Catalog\Api\CategoryLinkManagementInterface            $categoryLinkManagement
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable    $typeConfigurableProduct
     * @param \Magento\CatalogInventory\Api\StockRegistryInterfaceFactory     $stockRegistryFactory
     * @param \Magento\ConfigurableProduct\Api\LinkManagementInterfaceFactory $linkManagementFactory
     * @param \Magento\Eav\Model\Entity\Attribute\OptionFactory               $optionFactory
     * @param \Magento\Eav\Api\AttributeOptionManagementInterface             $attributeOptionManager
     * @param \Magento\Eav\Model\AttributeRepository                          $attributeRepository
     * @param LoggerInterface                                                 $logger
     *
     * @param \Encomage\ErpIntegration\Helper\Data                            $helper
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
        AttributeRepository $attributeRepository,
        LoggerInterface $logger,
        Data $helper
    )
    {

        $this->categoryResource = $categoryResource;
        $this->productResource = $productResource;
        $this->objectManager = $objectManager;
        $this->productFactory = $productFactory;
        $this->categoryLinkManagement = $categoryLinkManagement;
        $this->typeConfigurableProduct = $typeConfigurableProduct;
        $this->stockRegistryFactory = $stockRegistryFactory;
        $this->linkManagementFactory = $linkManagementFactory;
        $this->optionFactory = $optionFactory;
        $this->attributeOptionManager = $attributeOptionManager;
        $this->logger = $logger;


        $this->apiClient = new ApiClient($scopeConfig);

        $this->_attributesOptions['color'] = $entityAttribute->loadByCode('catalog_product', 'color')
                                                             ->getSource()
                                                             ->getAllOptions(false, true);

        $this->_attributesOptions['size'] = $entityAttribute->loadByCode('catalog_product', 'size')
                                                            ->getSource()
                                                            ->getAllOptions(false, true);

        $this->setAllCategories();

        try {
            $this->size_attribute_id = $attributeRepository->get(Product::ENTITY, 'size')->getAttributeId();
        } catch (NoSuchEntityException $e) {
            $this->size_attribute_id = false;
        }

        $this->helper = $helper;
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
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return bool
     */
    private function parseBody(ResponseInterface $response)
    {
        $products_data = $this->apiClient->parseBody($response);

        if (!empty($products_data)) {
            $this->products_data[] = $products_data;

            return true;
        }

        return false;
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

            $cat = $this->helper->getCategoriesCodes($erpCategoryCode);

            if ($cat->valid()) {
                $category = $cat->current()['category_path'];
            }
        }

        if (null !== $typeProduct) {
            if ('S' === $typeProduct) {

                $cat = $this->helper->getShoeCodes($erpSubCategoryCode);


                if ($cat->valid()) {
                    $subCategory = $cat->current()['shoe_category_value'];
                }
            } elseif ('B' === $typeProduct) {
                $cat = $this->helper->getBagsCodes($erpSubCategoryCode);

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
     * @return array
     */
    private function getAllCategories()
    {
        if (empty($this->all_categories)) {
            $this->setAllCategories();
        }

        return $this->all_categories;
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
                $this->logger->error($e->getMessage());

            } catch (StateException $e) {
                $this->logger->error($e->getMessage());
            }

            return false;
        }

        return false;
    }

    /**
     * Write to system.log
     *
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function execute()
    {
        $this->logger->info('Finish Cron Import');


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

            $this->dataProccesing($this->products_data);
        }

    }

    /**
     * @param array $products_data
     *
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     */
    private function dataProccesing(array $products_data)
    {
        $data = $this->helper->getErpProductsObjects($products_data);

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

                        $colors = $this->helper->getColorCode($datum->getModelColor());

                        if (!empty($colors)) {
                            $colors = array_column($colors, 'erp_color_value');

                            $product->setColor(reset($colors));
                        }
                    }

                    try {
                        $this->productResource->save($product);

                    } catch (Exception $e) {
                        $this->logger->error($e->getMessage());
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

                        $colors = $this->helper->getColorCode($datum->getModelColor());

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
                        $this->logger->error($e->getMessage());
                    }
                }
            }
        }
    }


    /**
     * @param ErpProduct                     $datum
     * @param \Magento\Catalog\Model\Product $_product
     * @param int                            $category_id
     *
     * @throws \Exception
     */
    private function buildConfigurableProduct($datum, $_product, $category_id)
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

                    $product->setUrlKey($this->helper->getUrlKey($settings['name'], $settings['category_name']));

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

}