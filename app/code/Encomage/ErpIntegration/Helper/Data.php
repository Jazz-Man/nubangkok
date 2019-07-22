<?php

namespace Encomage\ErpIntegration\Helper;

use Encomage\ErpIntegration\Model\Api\ErpProduct;
use function GuzzleHttp\json_decode;
use InvalidArgumentException;
use function iter\filter;
use function iter\map;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Tests\NamingConvention\true\string;

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
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        parent::__construct($context);


        $this->color_code       = $this->scopeConfig->getValue(self::COLOR_CODE);
        $this->bags_codes       = $this->scopeConfig->getValue(self::BAGS_CODES);
        $this->shoe_codes       = $this->scopeConfig->getValue(self::SHOE_CODES);
        $this->categories_codes = $this->scopeConfig->getValue(self::CATEGORIES_CODES);
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

        if ( ! empty($color_code)) {
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
    private function sanitizeKey(string $key): string
    {
        return str_replace([' ', ','], '-', strtolower($key));
    }
}
