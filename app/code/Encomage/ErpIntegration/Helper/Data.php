<?php

namespace Encomage\ErpIntegration\Helper;

use Encomage\ErpIntegration\Model\Api\ErpProduct;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\Eav\Model\Config;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\Store;
use function iter\map;

/**
 * Class Data.
 */
class Data extends AbstractHelper
{


    /**
     * @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute
     */
    private $colors;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute
     */
    private $size;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    private $productFactory;
    /**
     * @var \Encomage\ErpIntegration\Helper\SkuGenerator
     */
    private $skuGenerator;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category
     */
    private $categoryResource;
    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    private $cache;

    /**
     * @param Context                                         $context
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     *
     * @param \Magento\Catalog\Model\ResourceModel\Category   $categoryResource
     * @param \Magento\Framework\App\CacheInterface           $cache
     * @param \Magento\Eav\Model\Config                       $attributeConfig
     * @param \Magento\Catalog\Model\ProductFactory           $productFactory
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        Context $context,
        ProductRepositoryInterface $productRepository,
        CategoryResource $categoryResource,
        CacheInterface $cache,
        Config $attributeConfig,
        ProductFactory $productFactory
    ) {
        parent::__construct($context);

        $this->categoryResource = $categoryResource;
        $this->cache            = $cache;

        $this->productRepository = $productRepository;
        $this->productFactory    = $productFactory;

        $this->colors = $attributeConfig->getAttribute(Product::ENTITY, 'color');
        $this->size   = $attributeConfig->getAttribute(Product::ENTITY, 'size');


        $this->skuGenerator = new SkuGenerator();
    }

    /**
     * @param $sky
     *
     * @return \Magento\Catalog\Api\Data\ProductInterface|\Magento\Catalog\Model\Product
     */
    public function getProductBySky($sky)
    {
        try {
            $_product = $this->productRepository->get($sky, true, Store::DEFAULT_STORE_ID);

        } catch (NoSuchEntityException $e) {
            $_product = $this->productFactory->create();
        }

        return $_product;
    }

    /**
     * @param string $name
     *
     * @param array  $attributes
     *
     * @return string
     */
    public function generateProductSku($name, array $attributes = []): string
    {

        $attributes = array_merge([
            'erp' => 'configurable',
        ], $attributes);

        return $this->skuGenerator->generateProductSku($name, $attributes);
    }

    /**
     * @param string $color_name
     *
     * @return bool|string|null
     */
    public function getColorIdByName($color_name)
    {
        if ($this->colors->usesSource()) {
            try {
                return $this->colors->getSource()->getOptionId($color_name);
            } catch (LocalizedException $e) {
                return false;
            }
        }

        return false;
    }

    /**
     * @param string $size
     *
     * @return bool|string|null
     */
    public function getSizeIdByName($size)
    {
        if ($this->size->usesSource()) {
            try {
                return $this->size->getSource()->getOptionId($size);
            } catch (LocalizedException $e) {
                return false;
            }
        }

        return false;
    }


    /**
     * @param string $category_name
     *
     * @return string|false
     */
    public function getCategoryByPath(string $category_name)
    {
        $identifier = "erp_cat_to_magento_{$category_name}";

        $category_id = $this->cache->load($identifier);


        if (empty($category_id)) {
            $connection = $this->categoryResource->getConnection();

            $category_table = $this->categoryResource->getTable('catalog_category_entity_varchar');

            $select = $connection->select()
                                 ->from($category_table, 'entity_id')
                                 ->where('value = ?', $category_name)
                                 ->where('store_id = ?', Store::DEFAULT_STORE_ID)
                                 ->limit(1);

            $category_id = $connection->fetchOne($select);

            if ($category_id !== false) {
                $this->cache->save($category_id, $identifier);
            }

        }

        return $category_id;
    }

    /**
     * @param array $products_data
     *
     * @return \Encomage\ErpIntegration\Model\Api\ErpProduct[]|\Generator
     */
    public function getErpProductsObjects(array $products_data)
    {
        /** @var \Generator|ErpProduct[] $data */
        $data = map(static function ($value) {
            return new ErpProduct($value);
        }, $products_data);

        return $data;
    }

    /**
     * @param string $key
     *
     * @return string
     */
    public function sanitizeKey(string $key): string
    {
        return str_replace([' ', ','], '-', strtolower($key));
    }


    /**
     * @param int $precision
     *
     * @return string
     */
    public function testMemory($precision = 2): string
    {
        $bytes = memory_get_peak_usage();
        $units = ['b', 'kb', 'mb', 'gb', 'tb'];

        $bytes = max($bytes, 0);
        $pow   = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow   = min($pow, count($units) - 1);

        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
