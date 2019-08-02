<?php

namespace Encomage\ErpIntegration\Helper;

/**
 * Class SkuGenerator.
 */
class SkuGenerator
{
    public const SKU_ABSTRACT_SEPARATOR = '-';

    public const SKU_TYPE_SEPARATOR = '-';

    public const SKU_VALUE_SEPARATOR = '_';

    public const SKU_MAX_LENGTH = 255;

    /**
     * @var \Encomage\ErpIntegration\Helper\StringUtils
     */
    private $string;

    public function __construct()
    {
        $this->string = new StringUtils();
    }

    /**
     * @param       $name
     * @param array $attributes
     *
     * @return string
     */
    public function generateProductSku($name, array $attributes = []): string
    {
        $name = $this->string->cleanString($name);

        $concreteSku = $this->generateConcreteSkuFromAttributes($attributes);

        $concreteSku = $this->formatConcreteSku($name, $concreteSku);

        return $concreteSku;
    }

    /**
     * @param string $abstractSku
     * @param string $concreteSku
     *
     * @return string
     */
    protected function formatConcreteSku($abstractSku, $concreteSku): string
    {
        $sku = sprintf('%s%s%s', $abstractSku, static::SKU_ABSTRACT_SEPARATOR, $concreteSku);

        $formattedSku = $this->sanitizeSku($sku);
        $formattedSku = $this->string->substr($formattedSku, 0, static::SKU_MAX_LENGTH);

        $formattedSku = rtrim($formattedSku, implode('', [
            static::SKU_TYPE_SEPARATOR,
            static::SKU_VALUE_SEPARATOR,
        ]));

        return strtoupper($formattedSku);
    }

    /**
     * @param array $attributes
     *
     * @return string
     */
    protected function generateConcreteSkuFromAttributes(array $attributes = [])
    {
        $sku = '';

        if (!empty($attributes)) {
            foreach ($attributes as $type => $value) {
                $sku .= $type.static::SKU_TYPE_SEPARATOR.$value.static::SKU_VALUE_SEPARATOR;
            }
        }

        return rtrim($sku, static::SKU_VALUE_SEPARATOR);
    }

    /**
     *  - Transliterates from UTF-8 to ASCII character set
     *  - Removes all non Alphanumeric and (.,-,_) characters
     *  - Replaces all space characters with dashes
     *  - Replaces multiple dashes with single dash.
     *
     * @param string $sku
     *
     * @return string
     */
    protected function sanitizeSku($sku)
    {
        $sku = preg_replace("/[^a-zA-Z0-9\.\-\_]/", '', $this->string->trim($sku));
        $sku = preg_replace('/\s+/', '-', $sku);
        $sku = preg_replace('/(\-)\1+/', '$1', $sku);

        return $sku;
    }
}
