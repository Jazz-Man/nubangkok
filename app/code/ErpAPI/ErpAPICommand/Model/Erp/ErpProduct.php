<?php

namespace ErpAPI\ErpAPICommand\Model\Erp;

use Magento\Framework\Stdlib\StringUtils;

/**
 * Class Product.
 */
class ErpProduct
{

    public const SKU_MAX_LENGTH = 64;

    public const CHARSET_DEFAULT = 'UTF-8';

    private $LLLedTimeETodayyy;

    private $InfoUpdateDateAndTime;
    /**
     * @var string
     */
    private $IcProductCode;
    private $BarCode;
    /**
     * @var string
     */
    private $IcProductDescription0;
    private $IcUnitCode;
    private $ICCategoryCode;
    /**
     * @var int|float
     */
    private $SalesPrice;
    private $PropArtist;
    private $PropColor2;
    private $PropFormat;
    private $PropHeelHeight;
    private $PropHeelShape;
    private $PropLabel;
    private $PropModel;
    private $PropPrice1;
    private $PropVendorSUpplier;
    private $BranchCode;
    private $WarehouseCode;
    /**
     * @var string
     */
    private $IcCategoryName;
    /**
     * @var int
     */
    private $UnrestrictStock;
    private $ReserveStock;
    private $PPPGSBR_PRICE_;
    private $PPPGSBR_DISCOUNT_;
    private $PPD_DISCOUNT_;
    private $PPPGS_PRICE_;

    /**
     * @var StringUtils
     */
    private $string;

    /**
     * Product constructor.
     *
     * @param \stdClass $obj
     */
    public function __construct(\stdClass $obj)
    {
        $this->string = new StringUtils();
        foreach ($obj as $property => $value) {
            $this->$property = $value;
        }
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        $rawString    = mb_strtoupper($this->getIcProductCode(), self::CHARSET_DEFAULT);
        $has_space    = ! ctype_space($this->getIcProductCode());
        $is_uppercase = $this->getIcProductCode() === $rawString;

        return $has_space && $is_uppercase;
    }

    /**
     * @return string
     */
    public function getIcProductCode(): string
    {
        return $this->string->cleanString($this->IcProductCode);
    }

    /**
     * @return int
     */
    public function getStockStatus()
    {
        return (int)$this->UnrestrictStock > 0 ? 1 : 0;
    }

    /**
     * @return int
     */
    public function getUnrestrictStock(): int
    {
        return $this->UnrestrictStock;
    }

    /**
     * @return bool|string|null
     */
    public function getBarCode()
    {
        if ( ! empty($this->BarCode)) {
            if ((int)substr($this->BarCode, 3, 1) > 0) {
                return substr($this->BarCode, 0, 10);
            }

            return substr($this->BarCode, 0, 9);
        }

        return null;
    }

    /**
     * @return mixed|string
     */
    public function getConfigName()
    {
        if ( ! empty($this->IcProductDescription0)) {
            $result = explode(',', $this->IcProductDescription0);

            return array_shift($result);
        }

        return ' ';
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->IcProductDescription0;
    }

    /**
     * @return string
     */
    public function getCategoryName(): string
    {
        return $this->IcCategoryName;
    }

    /**
     * @return float|int|null
     */
    public function getSalesPrice()
    {
        return empty($this->SalesPrice) ? abs($this->SalesPrice) : null;
    }

    /**
     * @return bool|string
     */
    public function getUrlKey()
    {
        if ( ! empty($this->getName()) && ! empty($this->getCategoryName())) {
            $urlKey = "{$this->sanitizeKey($this->getCategoryName())}-{$this->sanitizeKey($this->getName())}";

            return trim($urlKey);
        }

        return false;
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
