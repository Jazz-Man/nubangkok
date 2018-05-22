<?php
namespace Encomage\ErpIntegration\Model\Api;

use Magento\Framework\Webapi\Exception;
use Zend\Http\Request as HttpRequest;
use Magento\Framework\Serialize\Serializer\Json as SerializerJson;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Psr\Log\LoggerInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as TypeConfigurableProduct;

use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;


/**
 * Class Product
 * @package Encomage\ErpIntegration\Model\Api
 */
class Product extends Request
{
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

    private $productResource;

    private $categoryResource;

    private $categoryLinkManagement;

    private $typeConfigurableProduct;

    private $json;

    private $logger;
    
    protected $rootCategoryList = [
        'woman/shoes' => 'WS',
        'woman/bags' => 'WB',
        'woman/clothing/dress' => 'WD',
        'woman/clothing/skirt' => 'WK',
        'woman/clothing/shorts' => 'WR',
        'woman/clothing/pants' => 'WP',
        'woman/clothing/tops' => 'WT',
        'woman/clothing/coal-jacket' => 'WC',
        'woman/accessories' => 'WA',
        'woman/accessories/belts' => 'WL',
        'woman/accessories/hats' => 'WH',
        'woman/accessories/scarf' => 'WF',
        'woman/accessories/earrings' => 'WE',
        'woman/accessories/jewelry' => 'WJ',
        'woman/accessories/sunglasses' => 'WG',

        'men/shoes' => 'MS',
        'men/bags' => 'MB',
        'men/bags/wallet' => 'MW',
        'men/accessories/hats' => 'MA',
        'men/accessories/necktie' => 'MN',
        'men/clothing/shirt' => 'MT',
        'men/clothing/pants' => 'MP',
        'men/clothing/shorts' => 'MR',
        'men/clothing/jacket' => 'MJ',

        'girl/shoes' => 'GS',
        'girl/bags' => 'GB',
        'girl/clothing/dress' => 'GD',
        'girl/clothing/skirt' => 'GK',
        'girl/clothing/shorts' => 'GR',
        'girl/clothing/pants' => 'GP',
        'girl/clothing/tops' => 'GT',

        'boy/shoes' => 'BS',
        'boy/clothing/shorts' => 'BR',
        'boy/clothing/pants' => 'BP',
        'boy/clothing/skirt' => 'BT'
    ];

    protected $categoryShoeType = [

        'flipper' => 'F',
        'sandal' => 'S',
        'ballet-flat' => 'B',
        'loafer' => 'L',
        'wedge' => 'W',
        'low-heel' => 'H',
        'pump-hi-heel' => 'P',
        'lace-up' => 'U',
        'boot' => 'T',
        'athletic' => 'A'
    ];

    protected $categoryBagsType = [
        'wallet-card' => 'W',
        'wristlet' => 'R',
        'phone' => 'P',
        'mini-midi' => 'M',
        'clutch' => 'C',
        'handbag' => 'H',
        'shoulder-cross' => 'S',
        'sling-hobo' => 'L',
        'sachel' => 'A',
        'tote' => 'T',
        'backpack' => 'B',
        'business' => 'Z',
        'weekender' => 'K',
        'luggage' => 'G'
    ];

    protected $colorsList = [
        'Metallic' => 'ML',
        'Silver' => 'NN',
        'Brown' => 'RW',
        'Grey' => 'GY',
        'Black' => 'BK',
        'Pink Gold' => 'PG',
        'Platinum' => 'PL',
        'Gold' => 'GL',
        'Red' => 'RD'
    ];

    protected $_attributesOptions = ['size' => [], 'color' => []];

    protected $_useBarCode = 16;
    
    protected $configurableProduct;

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
        LoggerInterface $logger
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
        $this->logger = $logger;
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function importAllProducts()
    {
        $this->setApiLastPoint('GetProductList');
        $this->setApiMethod(HttpRequest::METHOD_GET);
        $this->setAdditionalDataUrl([
            'Branchpricedisplay' => 1,
            "CategoryDisplaySubCat" => 1
        ]);
        $result = $this->sendApiRequest();

        if (empty($result) || !$result) {
            $this->logger->info('Response is empty');
            return $this;
        }
        $result = $this->json->unserialize($result);
        foreach ($result as $item) {
            $item = (is_object($item)) ? get_object_vars($item) : $item;

            $productId = $this->productResource->getIdBySku($item['IcProductCode']);
            if (strlen($item['IcProductCode']) >= 18 || $productId) {
                continue;
            }

            $confSku = $this->_prepareConfSku($item['BarCode']);
            $confName = $this->_prepareConfName($item['IcProductDescription0']);
            $categoryId = $this->_getCategoryId($item['BarCode']);

            $attributesOptions = $this->_getAttributesOptions($item['BarCode']);
            $color = $this->_getOptionByLabel($attributesOptions['colors'], 'color');
            $size = $this->_getOptionByLabel($attributesOptions['size'] / 10, 'size');
            
            if (!empty($confSku) && !empty($confName)) {
                if (!$this->configurableProduct || $this->configurableProduct->getSku() !== $confSku) {
                    $productId = $this->productResource->getIdBySku($confSku);
                    if (!$productId) {
                        $this->configurableProduct = $this->_createConfigurableProduct($confSku, $confName, $categoryId);
                    } else {
                        $this->configurableProduct = $this->productRepository->get($confSku);
                    }
                }
            }
            if ($this->configurableProduct) {

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
                        'is_in_stock' => Status::STATUS_ENABLED,
                        'qty' => $item['UnrestrictStock']
                    ]
                ]);
                $product->setColor($color['value']);
                $product->setSize($size['value']);
                $product = $this->productRepository->save($product);
                $this->categoryLinkManagement->assignProductToCategories($item['IcProductCode'], [$categoryId]);
            }
        }

        return $this;
    }

    /**
     * @param $barCode
     * @return null|string
     */
    protected function _prepareConfSku($barCode)
    {
        if (!empty($barCode)) {
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
//            $name = array_shift($result);
            return array_shift($result);
        }
        return null;
    }

    /**
     * @param $sku
     * @param $name
     * @param $categoryId
     * @return \Magento\Catalog\Model\Product
     */
    protected function _createConfigurableProduct($sku, $name, $categoryId)
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productFactory->create();
        $product->setSku($sku);
        $product->setName($name);
        $product->setTypeId(TypeConfigurableProduct::TYPE_CODE);
        $product->setAttributeSetId(Visibility::VISIBILITY_BOTH);
        $product->setCategoryIds([$categoryId]);
        $product->setColor(' ');
        $product->setSize(' ');
        $product = $this->productRepository->save($product);
        $this->categoryLinkManagement->assignProductToCategories($sku, [$categoryId]);
//        if ($this->colorAttribute && $this->sizeAttribute) {
//            $this->_setAttributeToConfProduct($product, [$this->colorAttribute, $this->sizeAttribute]);
//        }
        return $product;
    }

    /**
     * @param $barCode
     * @return mixed|null
     */
    protected function _getCategoryId($barCode)
    {
        if ($barCode) {
            $subCategory = '';
            $category = array_search(substr($barCode, 0, 2), $this->rootCategoryList);
            if (substr($barCode, 2, 1) == 'S') {
                $subCategory = array_search(substr($barCode, 2, 1), $this->categoryShoeType);
            } elseif (substr($barCode, 2, 1) == 'B') {
                $subCategory = array_search(substr($barCode, 2, 1), $this->categoryBagsType);
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
     * @param string $field
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
     * @param $field
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
    protected function _getAttributesOptions($barCode)
    {
        $result = [];
        $barCode = substr($barCode, 2);
        $barCode = preg_replace('/(\d+)/i', '${1},', $barCode);
        $barCode = explode(',', rtrim($barCode, ','));

        $options = (!empty(end($barCode)) && $last = array_pop($barCode)) ? $last : array_pop($barCode);
        $check = substr($options, -3) * 2;
        if ((bool)$check && gettype($check) == 'integer') {
            $result['size'] = substr($options, -3);
            $result['colors'][] = array_search(substr($options, 0, 2), $this->colorsList);
            $result['colors'][] = array_search(substr($options, 2, 2), $this->colorsList);
        } else {
            $result['size'] = null;
            $result['colors'][] = array_search(substr($options, 0, 2), $this->colorsList);
            $result['colors'][] = array_search(substr($options, 2, 2), $this->colorsList);
            $result['colors'][] = array_search(substr($options, 4, 2), $this->colorsList);
            $result['colors'][] = array_search(substr($options, 6, 2), $this->colorsList);
        }
        return $result;
    }

    /**
     * @param $label
     * @param $attrLabel
     * @return array|string
     */
    protected function _getOptionByLabel($label, $attrLabel)
    {
        if (!$this->_attributesOptions[$attrLabel]) {
            $attribute = $this->_getAttributeInfo('catalog_product', $attrLabel);
            $this->_attributesOptions[$attrLabel]['attribute'] = $attribute;
            $this->_attributesOptions[$attrLabel]['all_options'] = $attribute->getSource()->getAllOptions();
        }
        foreach ($this->_attributesOptions[$attrLabel]['all_options'] as $option) {
            if ($option['label'] == $label) {
                return $option;
            }
        }
        return array_shift($this->_attributesOptions[$attrLabel]['all_options']);
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