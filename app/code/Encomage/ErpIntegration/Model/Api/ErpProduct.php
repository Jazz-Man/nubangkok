<?php

namespace Encomage\ErpIntegration\Model\Api;

use Encomage\ErpIntegration\Helper\StringUtils;
use Magento\CatalogInventory\Model\Stock;
use stdClass;

/**
 * Class Product.
 */
class ErpProduct
{

    /**
     * @var string
     */
    private $PropFormat;
    /**
     * @var string
     */
    private $BarCode;
    /**
     * @var string
     */
    private $IcProductDescription0;
    /**
     * @var int|float
     */
    private $SalesPrice;
    /**
     * @var string
     */
    private $PropArtist;
    /**
     * @var float|int
     */
    private $GrossWeight;

    /**
     * @var int|float
     */
    private $Height;

    /**
     * @var int|float
     */
    private $Width;
    /**
     * @var float|int
     */
    private $NetWeight;
    /**
     * @var string
     */
    private $PropLabel;
    /**
     * @var string
     */
    private $PropModel;
    /**
     * @var string
     */
    private $IcCategoryName;

    /**
     * @var string
     */
    private $ICCategoryCode;
    /**
     * @var int
     */
    private $UnrestrictStock;
    /**
     * @var StringUtils
     */
    private $string;
    /**
     * @var bool
     */
    private $is_shoes;

    /**
     * Product constructor.
     *
     * @param \stdClass $obj
     */
    public function __construct(stdClass $obj)
    {
        $this->string = new StringUtils();
        foreach ($obj as $property => $value) {
            if (property_exists($this, $property)) {
                $this->$property = $value;
            }
        }

        $this->is_shoes = 'Shoes' === $this->getSubCategoryName();
    }

    /**
     * @return string
     */
    public function getSubCategoryName(): string
    {
        $name = $this->getCategoryName();

        $name = explode(' ', $name);

        if (\count($name) <= 1) {
            return false;
        }

        return end($name);
    }

    /**
     * @return string|bool
     */
    protected function getCategoryName(): string
    {
        if ( ! empty($this->IcCategoryName)) {
            return $this->prepareStringProps($this->IcCategoryName);
        }

        return false;
    }

    /**
     * @param mixed $prop
     *
     * @return bool|string
     */
    private function prepareStringProps($prop)
    {
        $prop = $this->cleanStringProp($prop);

        return $this->upperCaseWords($prop);
    }

    /**
     * @param mixed $prop
     *
     * @return string
     */
    private function cleanStringProp($prop): string
    {
        $prop = $this->string->cleanString($prop);
        $prop = $this->string->trim($prop);
        $prop = filter_var($prop, FILTER_SANITIZE_STRING);

        return $prop;
    }

    /**
     * @param string $name
     * @param array  $sourceSeparator
     *
     * @return bool|string
     */
    private function upperCaseWords(string $name, array $sourceSeparator = [])
    {
        $name = $this->string->upperCaseWords($name, $sourceSeparator, ' ');

        if (empty($name)) {
            return false;
        }

        return $name;
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        $product_code = $this->getBarCode();

        $rawString    = mb_strtoupper($product_code, 'UTF-8');
        $has_space    = ! ctype_space($product_code);
        $is_uppercase = $product_code === $rawString;

        $not_allowed_sku = [
            'WB21WPOMP1BKBKXXNN',
            'WB21WPOMP1MBRWXXNN',
            'WB21WPOMP1GYGYXXNN',
            'WB21WPOMP1BRBRXXNN',
            'WS23SFLS2A1BKXX350',
            'WS23SFLS2A1BKXX380',
            'WS23SFLS2A1GYXX340',
            'WS23SFLS2A1GYXX350',
            'WS23SFLS2A1GYXX360',
            'WS23SFLS2A1GYXX410',
            'WB51MTTLAY1BLGNXXLRG',
            'WB51MTTLAY1BLGNXXMED',
            'WB51MTTLAY1BLGNXXSML',
        ];

        return $has_space && $is_uppercase && ! \in_array($product_code, $not_allowed_sku, true);
    }

    /**
     * @return string
     */
    public function getBarCode(): string
    {
        return $this->cleanStringProp($this->BarCode);
    }

    /**
     * @return bool|mixed|string
     */
    public function getSize(): string
    {
        $prop = false;

        if ( ! empty($this->PropLabel)) {
            $prop = $this->string->cleanString($this->PropLabel);

            $prop = $this->string->trim($prop);

            $filter = FILTER_SANITIZE_STRING;

            if (is_numeric($prop)) {
                $prop_strlen = $this->string->strlen($prop);

                if ($prop_strlen > 2){
                    $prop = (int)$prop / 10;
                }

                $filter = FILTER_SANITIZE_NUMBER_INT;
            }

            $prop = filter_var($prop, $filter);
        }

        /*
         * filtering for "36 19/02"
         */

        if ( ! is_numeric($prop) && (bool)(int)$prop) {
            $prop = (int)$prop;
        }

        return $prop ?: 'no size';
    }

    /**
     * @return bool|string
     */
    public function getName()
    {
        if ( ! empty($this->IcProductDescription0)) {
            return $this->prepareStringProps($this->IcProductDescription0);
        }

        return false;
    }

    /**
     * @return int
     */
    public function getStockStatus(): int
    {
        return $this->UnrestrictStock > 0 ? Stock::STOCK_IN_STOCK : 0;
    }

    /**
     * @return int
     */
    public function getUnrestrictStock(): int
    {
        return $this->filterNumericProps($this->UnrestrictStock);
    }

    /**
     * @param string|float|int $prop
     *
     * @return string|int
     */
    private function filterNumericProps($prop)
    {
        $prop = $this->string->cleanString($prop);
        $prop = $this->string->trim($prop);

        if (\is_float($prop)) {
            $filter = FILTER_SANITIZE_NUMBER_FLOAT;
        } elseif (\is_int($prop)) {
            $filter = FILTER_SANITIZE_NUMBER_INT;
        } else {
            $filter = FILTER_DEFAULT;
        }

        return filter_var($prop, $filter);
    }

    /**
     * @return bool|string
     */
    public function getFormat()
    {
        if ( ! empty($this->PropFormat)) {
            return $this->prepareStringProps($this->PropFormat);
        }

        return false;
    }

    /**
     * @return string|bool
     */
    public function getGrossWeight()
    {
        if ( ! empty($this->GrossWeight)) {
            $props = $this->filterNumericProps($this->GrossWeight);

            return round($props, 2);
        }

        return false;
    }

    /**
     * @return string
     */
    public function getNetWeight(): string
    {
        return $this->filterNumericProps($this->NetWeight);
    }

    /**
     * @return mixed
     */
    public function getRootCategoryName(): string
    {
        $name = $this->getCategoryName();

        $name = explode(' ', $name);

        return reset($name);
    }

    /**
     * @return float|int|null
     */
    public function getSalesPrice()
    {
        if ( ! empty($this->SalesPrice)) {
            $prop = $this->filterNumericProps($this->SalesPrice);

            return abs($prop);
        }

        return null;
    }

    /**
     * @return string
     */
    public function getModel(): string
    {
        if ( ! empty($this->PropModel)) {
            return $this->prepareStringProps($this->PropModel);
        }

        return false;
    }

    /**
     * @return bool|string
     */
    public function getCategoryCode()
    {
        if ( ! empty($this->ICCategoryCode)) {
            return $this->cleanStringProp($this->ICCategoryCode);
        }

        return false;
    }

    /**
     * @return bool|string
     */
    public function getColor()
    {
        if ( ! empty($this->PropArtist)) {
            return $this->cleanStringProp($this->PropArtist);
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isShoes(): bool
    {
        return $this->is_shoes;
    }

    /**
     * @return float|int
     */
    public function getHeight()
    {
        if ( ! empty($this->Height)) {
            return $this->filterNumericProps($this->Height);
        }

        return false;
    }

    /**
     * @return float|int
     */
    public function getWidth()
    {
        if ( ! empty($this->Width)) {
            return $this->filterNumericProps($this->Width);
        }

        return false;
    }
}
