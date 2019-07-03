<?php

namespace ErpAPI\ErpAPICommand\Model\Erp;

use Magento\Framework\Stdlib\StringUtils;

/**
 * Class Product.
 */
class ErpProduct
{

    const CHARSET_DEFAULT = 'UTF-8';
    const SKU_MAX_LENGTH = 64;
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
     * @var bool|string
     */
    private $ModelColor = false;
    /**
     * @var bool|string
     */
    private $Size = false;
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

        $this->parsetColorAndSize();
    }

    private function parsetColorAndSize()
    {

        if ($this->isValid()) {
            $bar_code = $this->getBarCode();

            $strlen = $this->string->strlen($bar_code);

            switch ($strlen) {
                case 17:
                    $regex = '/(?P<Category>[A-Z]{2})(?P<SomeData>[A-Z0-9]{8})(?P<ModelColor>[A-Z]*)(?P<Size>[\d]*)/';

                    preg_match($regex, $bar_code, $matches);

                    if ( ! empty($matches)) {
                        $this->Size       = $matches['Size'];
                        $this->ModelColor = $matches['ModelColor'];
                    }

                    break;
                case 18:
                    $regex = '/(?P<Category>[A-Z]{2})(?P<SomeData>[A-Z0-9]{9})(?P<ModelColor>[A-Z]*)(?P<Size>[\d]*)/';

                    preg_match($regex, $bar_code, $matches);

                    if ( ! empty($matches['Size'])) {
                        $this->Size       = $matches['Size'];
                        $this->ModelColor = $matches['ModelColor'];

                    } else {
                        $regex = '/(?P<Category>[A-Z]{2})(?P<SomeData>[A-Z0-9]{5})(?P<Size>[\d]*)(?P<ModelColor>[A-Z]{4})/';

                        preg_match($regex, $bar_code, $matches);

                        if ( ! empty($matches)) {
                            $this->ModelColor = $matches['ModelColor'];
                            $this->Size       = $matches['Size'];
                        }
                    }


                    break;
                case 13:
                    $regex = '/(?P<Category>[A-Z]{2})(?P<SomeData>[A-Z0-9]{5})(?P<ModelColor>[A-Z]{4})(?P<Size>[\d]*)/';

                    preg_match($regex, $bar_code, $matches);

                    if ( ! empty($matches)) {
                        $this->ModelColor = $matches['ModelColor'];
                        $this->Size       = $matches['Size'];
                    }

                    break;
                case 16:
                    $regex = '/(?P<Category>[A-Z]{2})(?P<SomeData>[A-Z0-9]{7})(?P<ModelColor>[A-Z]{4})(?P<Size>[\d]*)/';

                    preg_match($regex, $bar_code, $matches);
                    if ( ! empty($matches)) {
                        $this->ModelColor = $matches['ModelColor'];
                        $this->Size       = $matches['Size'];
                    }
                    break;
                case 14:
                    $regex = '/(?P<SomeData>[A-Z0-9]{10})(?P<ModelColor>[A-Z]{2})(?P<Size>[\d]*)/';

                    preg_match($regex, $bar_code, $matches);

                    if ( ! empty($matches)) {

                        $this->Size = $matches['Size'];

                        switch ($matches['ModelColor']) {
                            case 'TU':
                                $_color = 'TRXXBK';
                                break;
                            case 'BK':
                                $_color = 'BKBF';
                                break;
                            case 'BR':
                                $_color = 'BRBR';
                                break;
                            case 'GN':
                                $_color = 'GNXX';
                                break;
                            case 'GY':
                                $_color = 'GYXX';
                                break;
                            case 'TN':
                                $_color = 'TNXX';
                                break;
                            default:
                                $_color = $matches['ModelColor'];
                                break;
                        }

                        $this->ModelColor = $_color;
                    }

                    break;

                case 20:
                default:
                    $this->ModelColor = false;
                    $this->Size       = false;
                    break;

            }


            if (!empty($this->Size) && $this->string->strlen($this->Size) === 2){
                $this->Size = "{$this->Size}0";
            }
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
     * @return mixed
     */
    public function getBarCode()
    {
        return $this->BarCode;
    }

    /**
     * @return string
     */
    public function getIcProductCode(): string
    {
        return $this->string->cleanString($this->IcProductCode);
    }

    /**
     * @return bool|string
     */
    public function getModelColor()
    {
        return $this->ModelColor;
    }

    /**
     * @return bool|string
     */
    public function getSize()
    {
        return $this->Size;
    }

    /**
     * @return int
     */
    public function getStockStatus()
    {
        return (int)$this->UnrestrictStock > 0 ? 1 : 0;
    }

    /**
     * @return string
     */
    public function getPropVendorSUpplier()
    {
        return $this->string->cleanString($this->PropVendorSUpplier);
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
    public function getConfigSku()
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
     * @return mixed
     */
    public function getICCategoryCode()
    {
        return $this->string->cleanString($this->ICCategoryCode);
    }

    /**
     * @return mixed
     */
    public function getPropFormat()
    {
        return $this->string->cleanString($this->PropFormat);
    }

    /**
     * @return mixed
     */
    public function getPropHeelHeight()
    {
        return $this->string->cleanString($this->PropHeelHeight);
    }

    /**
     * @return mixed
     */
    public function getPropLabel()
    {
        return $this->string->cleanString($this->PropLabel);
    }

    /**
     * @return string
     */
    public function getCategoryAcronym()
    {
        $words = preg_split("/[\s,_-]+/", $this->getCategoryName());

        $acronym = '';

        foreach ($words as $w) {
            $acronym .= $w[0];
        }

        return strtoupper($acronym);
    }

    /**
     * @return string
     */
    public function getCategoryName(): string
    {
        return $this->string->cleanString($this->IcCategoryName);
    }

    public function getOptions()
    {

    }

    /**
     * @return float|int|null
     */
    public function getSalesPrice()
    {
        return ! empty($this->SalesPrice) ? abs($this->SalesPrice) : null;
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
     * @return string
     */
    public function getName(): string
    {
        return $this->IcProductDescription0;
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
