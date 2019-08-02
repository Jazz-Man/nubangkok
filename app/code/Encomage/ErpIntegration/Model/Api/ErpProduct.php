<?php

namespace Encomage\ErpIntegration\Model\Api;

use DateTime;
use Encomage\ErpIntegration\Helper\StringUtils;
use stdClass;

/**
 * Class Product.
 */
class ErpProduct
{

    /**
     * @var string
     */
    private $LLLedTimeETodayyy;

    /**
     * @var string
     */

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
    /**
     * @var string
     */
    private $PropArtist;
    /**
     * @var string
     */
    private $PropColor2;
    private $PropFormat;
    private $PropHeelHeight;
    private $PropHeelShape;
    private $PropLabel;
    /**
     * @var string
     */
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
    public $Size = false;
    /**
     * @var StringUtils
     */
    private $string;

    /**
     * Product constructor.
     *
     * @param \stdClass $obj
     */
    public function __construct(stdClass $obj)
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

            if ( ! empty($this->Size) && 2 === $this->string->strlen($this->Size)) {
                $this->Size = "{$this->Size}0";
            }
        }
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        $rawString    = mb_strtoupper($this->getIcProductCode(), 'UTF-8');
        $has_space    = ! ctype_space($this->getIcProductCode());
        $is_uppercase = $this->getIcProductCode() === $rawString;

        return $has_space && $is_uppercase;
    }

    /**
     * @return mixed
     */
    public function getBarCode()
    {
        return $this->string->cleanString($this->BarCode);
    }

    /**
     * @return string
     */
    public function getIcProductCode(): string
    {
        return $this->string->cleanString($this->IcProductCode);
    }

    /**
     * @return bool|array
     */
    public function getModelColor()
    {
        if ( ! empty($this->ModelColor)) {
            $colors = $this->string->split($this->ModelColor, 2);

            return array_filter($colors, static function ($item) {
                return 'XX' !== $item;
            });
        }

        return $this->ModelColor;
    }

    /**
     * @return bool|string
     */
    public function getSize()
    {
        $size = filter_var($this->getName(), FILTER_SANITIZE_NUMBER_INT);

        if ( ! empty($size)) {
            $strlen = $this->string->strlen($size);

            switch ($strlen) {

                case $strlen > 4 && $this->string->strlen($this->getPropLabel()) === 2:

                    $size = "{$this->getPropLabel()}0";

                    break;
                case 4:
                    $size = $this->string->substr($size, 1);
                    break;
                case $strlen < 3:
                    $size = $this->getPropLabel();
                    break;
            }

            switch ((int)$size) {
                case 3:
                    $size = 'XS';
                    break;
            }

        }


        return $size;
    }

    /**
     * @return int
     */
    public function getStockStatus():int
    {
        return $this->UnrestrictStock > 0 ? 1 : 0;
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
        if ( ! empty($this->getBarCode())) {
            if ((int)$this->string->substr($this->getBarCode(), 3, 1) > 0) {
                return $this->string->substr($this->getBarCode(), 0, 10);
            }

            return $this->string->substr($this->getBarCode(), 0, 9);
        }

        return null;
    }


    /**
     * @return bool|string
     */
    public function getConfigName()
    {
        $search = [
            '',
        ];

        if ( ! empty($this->getColor1())) {
            $search[] = $this->getColor1();
        }

        if ( ! empty($this->getSize())) {
            $search[] = $this->getSize();
        }

        return $this->upperCaseWords($this->getName(), $search);
    }


    /**
     * @return string
     */
    public function getCategoryCode()
    {
        return $this->string->cleanString(strtoupper($this->ICCategoryCode));
    }


    /**
     * @return bool|string
     */
    public function getPropFormat()
    {
        $name = $this->string->cleanString($this->PropFormat);


        $name = preg_replace('/(S6|S4)/', 'Essence', $name);
        $name = preg_replace('/(Heel Fleet|Low Heel)/', 'Heel', $name);
        $name = preg_replace('/(Flat,Ballet|Ballet, Flat|Ballet,flat|Flat|Ballet)/', 'Ballet Flat', $name);


        return $this->upperCaseWords($name);
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
    public function getCategoryName(): string
    {

        $name = $this->upperCaseWords($this->IcCategoryName);

        return $this->string->cleanString($name);
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
     * @return string
     */
    public function getSubCategoryName(): string
    {
        $name = $this->getCategoryName();

        $name = explode(' ', $name);

        if (count($name) <= 1) {
            return false;
        }

        return end($name);
    }

    /**
     * @return float|int|null
     */
    public function getSalesPrice()
    {
        return ! empty($this->SalesPrice) ? abs($this->SalesPrice) : null;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        $name = $this->upperCaseWords($this->IcProductDescription0);

        return $this->string->cleanString($name);
    }

    /**
     * @return string
     */
    public function getPropModel(): string
    {
        $replace = [
            'H.'           => 'Heel.',
            'ESN'          => 'Essence',
            'slim21'       => 'Slim 21',
            'pompom'       => 'POM POM',
            'sw.ngoo'      => 'Swish Ngoo',
            'sw.nangPraya' => 'Swish nangPraya',
            'Simpicity'    => 'Simplicity',
        ];

        $model = $this->string->strReplaceArray($replace, $this->PropModel);

        $name = $this->upperCaseWords($model);

        return $this->string->cleanString($name);
    }


    /**
     * @return array|bool
     */
    public function getColor1()
    {
        return $this->buildColor($this->PropArtist);
    }


    /**
     * @return array|bool
     */
    public function getColor2()
    {
        return $this->buildColor($this->PropColor2);
    }


    /**
     * @return bool|\DateTime
     */
    public function getCreatedDate()
    {
        try {
            $date = $this->getDate($this->LLLedTimeETodayyy);
        } catch (\Exception $e) {
            $date = false;
        }

        return $date;
    }

    /**
     * @return bool|\DateTime
     */
    public function getUpdateDate()
    {
        try {
            $date = $this->getDate($this->InfoUpdateDateAndTime);
        } catch (\Exception $e) {
            $date = false;
        }

        return $date;
    }

    /**
     * @param string $color_name
     *
     * @return bool|string
     */
    private function buildColor($color_name)
    {

        $color_name = trim($color_name);

        if (empty($color_name) || $color_name === '(no color)') {
            return false;
        }

        return $this->upperCaseWords($color_name);
    }

    /**
     * @param string $timestamp
     *
     * @return \DateTime
     *
     * @throws \Exception
     */
    private function getDate(string $timestamp): DateTime
    {
        $timestamp = str_replace(['/Date(', ')/'], '', $timestamp);

        $date = new DateTime();
        $date->setTimestamp($timestamp);

        return $date;
    }

    /**
     * @param string $name
     *
     * @param array  $sourceSeparator
     *
     * @return bool|string
     */
    private function upperCaseWords(string $name, array $sourceSeparator = [])
    {

        $name = $this->string->upperCaseWords($name, $sourceSeparator,' ');

        if (empty($name)) {
            return false;
        }

        return $name;
    }


}
