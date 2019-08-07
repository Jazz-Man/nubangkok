<?php

namespace Encomage\ErpIntegration\Model\Api;

use DateTime;
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
    private $LLLedTimeETodayyy;

    /**
     * @var string
     */

    private $InfoUpdateDateAndTime;
    /**
     * @var string
     */
    private $IcProductCode;
    /**
     * @var string
     */
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
    /**
     * @var string
     */
    private $PropHeelHeight;
    /**
     * @var string
     */
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
            $this->$property = $value;
        }


        $this->is_shoes = $this->getSubCategoryName() === 'Shoes';

    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        $rawString    = mb_strtoupper($this->getIcProductCode(), 'UTF-8');
        $has_space    = ! ctype_space($this->getIcProductCode());
        $is_uppercase = $this->getIcProductCode() === $rawString;

        return $has_space && $is_uppercase;
    }


    /**
     * @return string
     */
    public function getBarCode(): string
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
     * @return bool|mixed|string
     */
    public function getSize(): string
    {
        $size_from_name = filter_var($this->getName(), FILTER_SANITIZE_NUMBER_INT);

        $size = 'no size';

        if ( ! empty($size_from_name)) {

            switch (true) {
                case empty($this->getPropLabel()):
                    $size = $size_from_name;
                    break;

                case $this->getPropLabel() === $size_from_name:
                    $size = $this->getPropLabel();

                    break;

                default:
                    $size = $this->getPropLabel();

                    break;
            }

        } elseif ( ! empty($this->getPropLabel())) {

            $size = $this->getPropLabel();
        }

        if ( ! empty($size)) {
            $regex = '/(?P<size>[\d]{1,2})/';

            preg_match($regex, $size, $matches);

            if ( ! empty($matches)) {
                $size = $matches['size'];
            }

        }


        return $size;
    }

    /**
     * @return int
     */
    public function getStockStatus(): int
    {
        return $this->UnrestrictStock > 0 ? Stock::STOCK_IN_STOCK : 0;
    }

    /**
     * @return string
     */
    public function getPropVendorSUpplier(): string
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
    public function getCategoryCode(): string
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
     * @return string
     */
    public function getHeelHeight(): string
    {
        return $this->fixHeelProps($this->PropHeelHeight);
    }

    /**
     * @return string
     */
    public function getHeelShape(): string
    {
        return $this->fixHeelProps($this->PropHeelShape);
    }

    /**
     * @param string $prop
     *
     * @return string
     */
    private function fixHeelProps(string $prop): string
    {

        $prop = $this->string->cleanString($prop);

        $prop = preg_replace("/\([^)]+\)/", '', $prop);

        $prop = $this->string->stripAllCharacters($prop);

        $prop = $this->string->trim($prop);

        if (empty($prop) && $this->isShoes()) {
            $prop = 'Standard';
        }

        return $prop;
    }


    /**
     * @return string
     */
    public function getPropLabel(): string
    {
        $prop = $this->string->cleanString($this->PropLabel);

        return $this->string->trim($prop);
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
     * @return bool|string
     */
    public function getColor1()
    {
        return $this->buildColor($this->PropArtist);
    }


    /**
     * @return bool|string
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
            return $this->upperCaseWords('no color');
        }

        $replace = [
            'Bluegreen'       => 'Blue Green',
            'Baff'            => 'Buff',
            'Turguoise'       => 'Turquoise',
            'D.'              => 'Dark.',
            'Mahogany Irides' => 'Mahogany',
            'Black Irides'    => 'Irridesce Black',
        ];


        $color_name = $this->string->strReplaceArray($replace, $color_name);

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

        $name = $this->string->upperCaseWords($name, $sourceSeparator, ' ');

        if (empty($name)) {
            return false;
        }

        return $name;
    }

    /**
     * @return bool
     */
    public function isShoes(): bool
    {
        return $this->is_shoes;
    }

}
