<?php

namespace Encomage\ErpIntegration\Helper;

use Encomage\ErpIntegration\Model\Api\ErpProduct;
use function GuzzleHttp\json_decode;
use InvalidArgumentException;
use function iter\filter;
use function iter\map;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\AttributeRepository;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Store\Model\Store;
use Magento\Eav\Model\Entity\Attribute;

/**
 * Class Data.
 */
class Data extends AbstractHelper
{
    const XML_PATH_PHP_TIME_LIMIT = 'erp_etoday_settings/erp_authorization/time_limit';

    const COLOR_CODE = 'erp_etoday_settings/color_settings/color_code';

    const BAGS_CODES = 'erp_etoday_settings/category_type_bags/bags_codes';

    const SHOE_CODES = 'erp_etoday_settings/category_type_shoe/shoe_codes';

    const CATEGORIES_CODES = 'erp_etoday_settings/categories/categories_codes';

    private $color_code;
    private $bags_codes;
    private $shoe_codes;
    private $categories_codes;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;
    /**
     * @var \Magento\Eav\Model\AttributeRepository
     */
    private $attributeRepository;
    /**
     * @var int|null
     */
    private $size_attribute_id;
    /**
     * @var Collection
     */
    private $attributeOptionCollection;

    /**
     * @param Context                                   $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Eav\Model\AttributeRepository    $attributeRepository
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        AttributeRepository $attributeRepository
    )
    {
        parent::__construct($context);

        $this->attributeOptionCollection = $objectManager->create(Collection::class);

        $this->color_code       = $this->scopeConfig->getValue(self::COLOR_CODE);
        $this->bags_codes       = $this->scopeConfig->getValue(self::BAGS_CODES);
        $this->shoe_codes       = $this->scopeConfig->getValue(self::SHOE_CODES);
        $this->categories_codes = $this->scopeConfig->getValue(self::CATEGORIES_CODES);


        try {
            $this->size_attribute_id = $attributeRepository->get(Product::ENTITY, 'size')->getAttributeId();
        } catch (NoSuchEntityException $e) {
            $this->size_attribute_id = false;
        }

    }

    /**
     * @return mixed
     */
    public function getTimeLimit()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_PHP_TIME_LIMIT);
    }

    /**
     * @param array $codes
     *
     * @return array|bool
     */
    public function getColorCode(array $codes)
    {
        $color_code = $this->jsonDecode($this->color_code);

        if (! empty($color_code)) {
            $color_code = array_values($color_code);

            $colors = array_filter($color_code, static function ($item) use ($codes) {
                return in_array($item['erp_color_code'], $codes);
            });

            return $colors;
        }

        return false;
    }

    /**
     * @param $code
     *
     * @return array|mixed
     */
    public function getBagsCodes($code)
    {
        $data = $this->jsonDecode($this->bags_codes);

        return $this->filter($data, 'erp_bags_code', $code);
    }

    /**
     * @param $code
     *
     * @return \Iterator
     */
    public function getShoeCodes($code)
    {
        $data = $this->jsonDecode($this->shoe_codes);

        return $this->filter($data, 'erp_shoe_code', $code);
    }

    /**
     * @param string $code
     *
     * @return \Iterator
     */
    public function getCategoriesCodes($code)
    {
        $categories_codes = $this->jsonDecode($this->categories_codes);

        return $this->filter($categories_codes, 'erp_category_code', $code);
    }

    /**
     * @param        $object
     * @param string $key
     * @param string $value
     *
     * @return \Iterator
     */
    private function filter($object, string $key, string $value)
    {
        return filter(static function ($item) use ($key, $value) {
            return $item[$key] === $value;
        }, $object);
    }

    /**
     * @param string $json
     *
     * @return array|mixed
     */
    private function jsonDecode($json)
    {
        try {
            $data = json_decode($json, true);
        } catch (InvalidArgumentException $e) {
            $data = [];
        }

        return $data;
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
     * @param string $name
     * @param string $category_name
     *
     * @return string
     */
    public function getUrlKey($name, $category_name)
    {
        $urlKey = "{$this->sanitizeKey($category_name)}-{$this->sanitizeKey($name)}";

        return trim($urlKey);
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
     * @param string $string
     * @param null|string   $id
     *
     * @return string
     */
    public function skuGen(string $string, $id = null){
        $results = ''; // empty string
        $vowels = ['a', 'e', 'i', 'o', 'u', 'y']; // vowels
        preg_match_all('/[A-Z][a-z]*/', ucfirst($string), $m); // Match every word that begins with a capital letter, added ucfirst() in case there is no uppercase letter
        foreach($m[0] as $substring){
            $substring = str_replace($vowels, '', strtolower($substring)); // String to lower case and remove all vowels

            $strlen = mb_strlen($substring, StringUtils::ICONV_CHARSET);

            $results .= preg_replace('/([a-z]{'.$strlen.'})(.*)/', '$1', $substring); // Extract the first N letters.
        }
        $results .= '-'. str_pad($id, 4, 0, STR_PAD_LEFT); // Add the ID
        return $results;
    }

    /**
     * @return array
     */
    public function getSizeOptions(): array
    {
        return $this->attributeOptionCollection
            ->setAttributeFilter($this->size_attribute_id)
            ->setStoreFilter(Store::DEFAULT_STORE_ID)
            ->load()
            ->getData();
    }

    /**
     * @return int|null
     */
    public function getSizeAttributeId(): ?int
    {
        return $this->size_attribute_id;
    }
}
