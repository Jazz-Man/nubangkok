<?php
namespace Encomage\ErpIntegration\Model\Api;

use Magento\Framework\Webapi\Exception;
use Zend\Http\Request as HttpRequest;
use Magento\Framework\Serialize\Serializer\Json as SerializerJson;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as TypeConfigurableProduct;
use Magento\ConfigurableProduct\Api\LinkManagementInterfaceFactory;
use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\CatalogInventory\Api\StockRegistryInterfaceFactory;

/**
 * Class Product
 * @package Encomage\ErpIntegration\Model\Api
 */
class Product extends Request
{
    const STATUS_IN_STOCK = 1;
    const STATUS_OUT_OF_STOCK = 0;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;
    /**
     * @var ProductInterface
     */
    private $productFactory;
    /**
     * @var Attribute
     */
    private $entityAttribute;
    /**
     * @var ProductResource
     */
    private $productResource;
    /**
     * @var CategoryResource
     */
    private $categoryResource;
    /**
     * @var CategoryLinkManagementInterface
     */
    private $categoryLinkManagement;
    /**
     * @var TypeConfigurableProduct
     */
    private $typeConfigurableProduct;
    /**
     * @var SerializerJson
     */
    private $json;
    /**
     * @var LinkManagementInterfaceFactory
     */
    private $linkManagementFactory;
    /**
     * @var StockRegistryInterfaceFactory
     */
    private $stockRegistryFactory;
    /**
     * @var array
     */
    protected $_attributesOptions = ['size' => [], 'color' => []];
    /**
     * @var int
     */
    protected $_useBarCode = 16;
    /**
     * @var array
     */
    protected $categoryCodes;
    /**
     * @var array
     */
    protected $subCategoryCodesShoe;
    /**
     * @var array
     */
    protected $subCategoryCodesBags;
    /**
     * @var array
     */
    protected $colorCodes;

    /**
     * Product constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param ProductRepositoryInterface $productRepository
     * @param ProductFactory $productFactory
     * @param Attribute $entityAttribute
     * @param ProductResource $productResource
     * @param CategoryLinkManagementInterface $categoryLinkManagement
     * @param CategoryResource $categoryResource
     * @param TypeConfigurableProduct $typeConfigurableProduct
     * @param SerializerJson $json
     * @param LinkManagementInterfaceFactory $linkManagementFactory
     * @param StockRegistryInterfaceFactory $stockRegistryFactory
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ProductRepositoryInterface $productRepository,
        ProductFactory $productFactory,
        Attribute $entityAttribute,
        ProductResource $productResource,
        CategoryLinkManagementInterface $categoryLinkManagement,
        CategoryResource $categoryResource,
        TypeConfigurableProduct $typeConfigurableProduct,
        SerializerJson $json,

        LinkManagementInterfaceFactory $linkManagementFactory,
        StockRegistryInterfaceFactory $stockRegistryFactory
    )
    {
        parent::__construct($scopeConfig, $json);
        $this->productRepository = $productRepository;
        $this->productFactory = $productFactory;
        $this->entityAttribute = $entityAttribute;
        $this->productResource = $productResource;
        $this->categoryLinkManagement = $categoryLinkManagement;
        $this->categoryResource = $categoryResource;
        $this->typeConfigurableProduct = $typeConfigurableProduct;
        $this->json = $json;
        $this->linkManagementFactory = $linkManagementFactory;
        $this->stockRegistryFactory = $stockRegistryFactory;
    }

    /**
     * @param int $i don't set this param. Default must by 0
     * @return bool
     * @throws \Exception
     */
    public function importAllProducts($i = 0)
    {
        $this->setApiLastPoint('GetProductList');
        $this->setApiMethod(HttpRequest::METHOD_GET);
        $this->setAdditionalDataUrl([
            'Branchpricedisplay' => 1,
            "CategoryDisplaySubCat" => 1,
            "Page" => $i
        ]);
        try {
            $result = $this->sendApiRequest();
        } catch (\Exception $e) {
            throw new \Exception(__($e->getMessage()));
        }
        if (empty($result)) {
            if ($i == 0) {
                throw new \Exception(__('The ERP system sent an empty response.'));
            }
            return true;
        }
        $configurable = [];
        $colorsNotExist = '';
        $sizeNotExist = '';

        foreach ($result as $item) {
            $item = (is_object($item)) ? get_object_vars($item) : $item;
            if (strlen($item['IcProductCode']) > 18 || strlen($item['IcProductCode']) < 16) {
                continue;
            }
            $productId = $this->productResource->getIdBySku($item['IcProductCode']);
            $stockStatus = ((int)$item['UnrestrictStock'] > 0) ? self::STATUS_IN_STOCK : self::STATUS_OUT_OF_STOCK;
            if ($productId) {

                /** @var \Magento\Catalog\Model\Product $product */
                $product = $this->productFactory->create()->load($productId);
                $product->setPrice($item['salesprice']);
                $product->addData([
                    'quantity_and_stock_status' => [
                        'is_in_stock' => $stockStatus,
                        'qty' => $item['UnrestrictStock']
                    ]
                ]);
                $this->productRepository->save($product);
                continue;
            }
            $confSku = $this->_prepareConfSku($item['BarCode']);
            $confName = $this->_prepareConfName($item['IcProductDescription0']);
            $categoryIds = $this->_getCategoryId($item['BarCode']);
            $attributesOptions = $this->_getAttributesCodes($item['BarCode']);
            $color = $this->_getAttributeIdByLabel($attributesOptions['colors'], 'color');
            $size = $this->_getAttributeIdByLabel($attributesOptions['size'] / 10, 'size');
            if (empty($color['value'])) {
                $colorsNotExist .= $item['IcProductDescription0'] . ' - ';
                $colorsNotExist .= $attributesOptions['colors'] . ', ';
                continue;
            }
            if (substr($item['BarCode'],1,1) !== 'B' && empty($size['value'])) {
                $sizeNotExist .= $item['IcProductDescription0'] . ', ';
                continue;
            }
            /** @var \Magento\Catalog\Model\Product $product */
            $product = $this->productFactory->create();
            $product->setSku($item['IcProductCode']);
            $product->setName($item['IcProductDescription0']);
            $product->setAttributeSetId(Visibility::VISIBILITY_BOTH);
            $product->setVisibility(Visibility::VISIBILITY_NOT_VISIBLE);
            $product->setTypeId('simple');
            $product->setPrice($item['salesprice']);
            $product->setWeight(null);
            $product->addData([
                'quantity_and_stock_status' => [
                    'is_in_stock' => $stockStatus,
                    'qty' => $item['UnrestrictStock']
                ]
            ]);
            $product->setColor($color['value']);
            $product->setSize($size['value']);
            if ($urlKey = $this->_prepareUrlKey($item['IcProductDescription0'], $item['IcCategoryName'])) {
                $product->setUrlKey($urlKey);
            }
            try {
                $product = $this->productRepository->save($product);
                $this->categoryLinkManagement->assignProductToCategories($item['IcProductCode'], [$categoryIds]);
            } catch (Exception $e) {
                throw new \Exception(__($e->getMessage()));
            }

            if (!empty($confSku) && !empty($confName) && !array_key_exists($confSku, $configurable)) {
                $configurable[$confSku] = ['name' => $confName, 'category_ids' => $categoryIds];
                $configurable[$confSku]['category_name'] = $item['IcCategoryName'];
            }
            if ($product->getId() && array_key_exists($confSku, $configurable)) {
                $configurable[$confSku]['associate_ids'][$product->getId()] = $product->getId();
                $configurable[$confSku]['skus'][] = $product->getSku();
            }
            $configurable[$confSku]['color'] = (array_key_exists('color', $configurable[$confSku])) ? $configurable[$confSku]['color'] : '';
            if ($configurable[$confSku]['color'] == null) {
                $configurable[$confSku]['color'] = ($product->getColor()) ? $product->getColor() : null;
            }
            $configurable[$confSku]['size'] = (array_key_exists('size', $configurable[$confSku])) ? $configurable[$confSku]['size'] : '';
            if ($configurable[$confSku]['size'] == null) {
                $configurable[$confSku]['size'] = ($product->getSize()) ? $product->getSize() : null;
            }
        }
        if (count($configurable) > 0) {
            foreach ($configurable as $sku => $settings) {
                try {
                    $this->_createConfigurableProduct($sku, $settings);
                } catch (Exception $e) {
                    throw new \Exception(__($e->getMessage()));

                }
            }
        }
        if (!empty($colorsNotExist)) {
            throw new \Exception(
                __('Color is not exist. Please add new color for this product - %1, and try again.', rtrim($colorsNotExist, ', '))
            );
        }
        if (!empty($sizeNotExist)) {
            throw new \Exception(
                __('Size is not exist. Please add new size for this product - %1, and try again.', rtrim($sizeNotExist, ', '))
            );
        }
        $i++;
        $this->importAllProducts($i);
    }

    /**
     * @param $barCode
     * @return null|string
     */
    protected function _prepareConfSku($barCode)
    {
        if (!empty($barCode)) {
            if ((int)substr($barCode, 3, 1) > 0) {
                return substr($barCode, 0, 10);
            }
            return substr($barCode, 0, 9);
        }
        return null;
    }

    /**
     * @param $name
     * @return mixed|null
     */
    protected function _prepareConfName($name)
    {
        if (!empty($name)) {
            $result = explode(',', $name);
            return array_shift($result);
        }
        return ' ';
    }

    /**
     * @param $sku
     * @param $settings
     * @return bool
     * @throws \Exception
     */
    protected function _createConfigurableProduct($sku, $settings)
    {
        $productId = $this->productResource->getIdBySku($sku);
        if (!$productId) {
            /** @var \Magento\Catalog\Model\Product $product */
            $product = $this->productFactory->create();
            $product->setSku($sku);
            $product->setName($settings['name']);
            $product->setTypeId(TypeConfigurableProduct::TYPE_CODE);
            $product->setAttributeSetId(Visibility::VISIBILITY_BOTH);
            $product->setCategoryIds([$settings['category_ids']]);
            $product->setColor(' ');
            $product->setSize(' ');
            if (substr($sku,1,1) == 'S') {
                $product->setAskAboutShoeSize(1);
            }
            if ($urlKey = $this->_prepareUrlKey($settings['name'], $settings['category_name'])) {
                $product->setUrlKey($urlKey);
            }
            $attributes = [];
            if ($settings['color']) {
                $attributes[] = $this->productResource->getAttribute('color')->getId();
            }
            if ($settings['size']) {
                $attributes[] = $this->productResource->getAttribute('size')->getId();
            }
            $product->getTypeInstance()->setUsedProductAttributeIds($attributes, $product);

            $configurableAttributesData = $product->getTypeInstance()->getConfigurableAttributesAsArray($product);
            $product->setCanSaveConfigurableAttributes(true);
            $product->setConfigurableAttributesData($configurableAttributesData);
            $configurableProductsData = [];
            $product->setConfigurableProductsData($configurableProductsData);

            try {
                $productId = $this->productRepository->save($product)->getId();
                $this->categoryLinkManagement->assignProductToCategories($sku, [$settings['category_ids']]);
            } catch (Exception $e) {
                throw new \Exception(__($e->getMessage()));
            }
        }
        if ($settings['associate_ids']) {
            foreach ($settings['skus'] as $childSku) {
                $this->_addAssociatedProducts($sku, $childSku);
            }
            if ($productId) {
                /** @var \Magento\CatalogInventory\Model\Stock\Item $stockItem */
                $stockItem = $this->stockRegistryFactory->create()->getStockItem($productId);
                if ($stockItem->getItemId()) {
                    $stockItem->setIsInStock(true);
                    $stockItem->setStockStatusChangedAutomaticallyFlag(true);
                    try {
                        $stockItem->save();
                    } catch (Exception $e) {
                        throw new \Exception(__($e->getMessage()));
                    }
                }
            }
        }
        return true;
    }

    /**
     * @param $sku
     * @param $childSku
     */
    protected function _addAssociatedProducts($sku, $childSku)
    {
        /** @var \Magento\ConfigurableProduct\Api\LinkManagementInterface $linkManagement */
        $linkManagement = $this->linkManagementFactory->create();
        $linkManagement->addChild($sku, $childSku);
    }

    /**
     * @param $barCode
     * @return mixed|null
     */
    protected function _getCategoryId($barCode)
    {
        if ($barCode) {
            $category = '';
            $subCategory = '';
            if (empty($this->categoryCodes)) {
                $this->categoryCodes = $this->json->unserialize($this->_getCategoryCodes());
            }
            $erpCategoryCode = substr($barCode, 0, 2);
            foreach ($this->categoryCodes as $categoryCode) {
                if ($categoryCode['erp_category_code'] == $erpCategoryCode) {
                    $category = $categoryCode['category_path'];
                    break;
                }
            }
            $typeProduct = substr($barCode, 1, 1);
            $erpSubCategoryCode = substr($barCode, 2, 1);
            if ($typeProduct == 'S') {
                if (empty($this->subCategoryCodesShoe)) {
                    $this->subCategoryCodesShoe = $this->json->unserialize($this->_getShoeCodes());
                }
                foreach ($this->subCategoryCodesShoe as $subCategoryCode) {
                    if ($subCategoryCode['erp_shoe_code'] == $erpSubCategoryCode) {
                        $subCategory = $subCategoryCode['shoe_category_value'];
                        break;
                    }
                }
            } elseif ($typeProduct == 'B') {
                if (empty($this->subCategoryCodesBags)) {
                    $this->subCategoryCodesBags = $this->json->unserialize($this->_getBagsCodes());
                }
                foreach ($this->subCategoryCodesBags as $subCategoryCode) {
                    if ($subCategoryCode['erp_bags_code'] == $erpSubCategoryCode) {
                        $subCategory = $subCategoryCode['bags_category_value'];
                        break;
                    }
                }
            }
            if (!empty($category) && !empty($subCategory)) {
                $category .= '/' . $subCategory;
            }
            return $this->_getCategoryByValue($category);
        }
        return null;
    }

    /**
     * @param $categoryFieldValue
     * @return mixed
     */
    protected function _getCategoryByValue($categoryFieldValue)
    {
        $result = $this->_sendCategoryRequest($categoryFieldValue);

        if (count($result) == 0) {
            $arrCategory = explode('/', $categoryFieldValue);
            foreach ($arrCategory as $value) {
                array_pop($arrCategory);
                $result = $this->_sendCategoryRequest(implode('/', $arrCategory));
                if (count($result) > 0) {
                    break;
                }
                if (count($arrCategory) == 0) {
                    $result = $this->_sendCategoryRequest('default-category');
                }
            }
        }

        $category = array_shift($result);
        return $category['entity_id'];
    }

    /**
     * @param $categoryFieldValue
     * @return array
     */
    protected function _sendCategoryRequest($categoryFieldValue)
    {
        $select = $this->categoryResource
            ->getConnection()
            ->select()
            ->from($this->categoryResource->getTable('catalog_category_entity_varchar'))
            ->where('value = ?', $categoryFieldValue);
        $result = $this->categoryResource->getConnection()->fetchAll($select);

        return $result;
    }

    /**
     * @param $barCode
     * @return array
     */
    protected function _getAttributesCodes($barCode)
    {
        $result = [];

        $barCode = substr($barCode, 2);
        $barCode = preg_replace('/(\d+)/i', '${1},', $barCode);
        $barCode = explode(',', rtrim($barCode, ','));

        $options = (!empty(end($barCode)) && $last = array_pop($barCode)) ? $last : array_pop($barCode);
        $check = substr($options, -3) * 2;
        if ((bool)$check && gettype($check) == 'integer') {
            $result['size'] = substr($options, -3);
            $erpColorCode = substr($options, -8, 4);
        } else {
            $result['size'] = null;
            $erpColorCode = substr($options, -6, 4);
        }
        if (empty($this->colorCodes)) {
            $this->colorCodes = $this->json->unserialize($this->_getColorCodes());
        }
        foreach ($this->colorCodes as $colorCode) {
            if ($colorCode['erp_color_code'] == $erpColorCode) {
                $result['colors'] = $colorCode['color_name'];
                break;
            }
        }
        if (empty($result['colors'])) {
            $result['colors'] = $erpColorCode;
        }
        return $result;
    }

    /**
     * @param $label
     * @param $attrName
     * @return mixed
     */
    protected function _getAttributeIdByLabel($label, $attrName)
    {
        if (!$this->_attributesOptions[$attrName]) {
            $attribute = $this->_getAttributeInfo('catalog_product', $attrName);
            $this->_attributesOptions[$attrName]['all_options'] = $attribute->getSource()->getAllOptions();
        }
        foreach ($this->_attributesOptions[$attrName]['all_options'] as $option) {
            if ($option['label'] == $label) {
                return $option;
            }
        }
        return ['label' => " ", 'value' => ""];
    }

    /**
     * @param $productName
     * @param $categoryName
     * @return bool|string
     */
    protected function _prepareUrlKey($productName, $categoryName)
    {
        if (!empty($productName) && !empty($categoryName)) {
            $urlKey = str_replace([' ', ','], '-', mb_strtolower($categoryName))
                . '-' . str_replace([' ', ','], '-', mb_strtolower($productName));
            return trim($urlKey);
        }
        return false;
    }

    /**
     * @param $entityType
     * @param $attributeCode
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getAttributeInfo($entityType, $attributeCode)
    {
        return $this->entityAttribute
            ->loadByCode($entityType, $attributeCode);
    }

    /**
     * @param string $point
     * @return string
     */
    public function setApiLastPoint($point = 'GetProductList')
    {
        return $this->apiLastPoint = $point;
    }

    /**
     * @param string $method
     * @return string
     */
    public function setApiMethod($method = HttpRequest::METHOD_GET)
    {
        return $this->apiMethod = $method;
    }

    /**
     * @param array $data
     * @return array
     */
    public function setAdditionalDataUrl(array $data = [])
    {
        return $this->additionalDataUrl = $data;
    }

    /**
     * @param array $content
     * @return array
     */
    public function setAdditionalDataContent(array $content = [])
    {
        return $this->additionalDataContent = $content;
    }
}