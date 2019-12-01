<?php

namespace Encomage\ErpIntegration\Helper;

use Magento\Framework\Stdlib\StringUtils as StringUtilsAlias;

/**
 * Class StringUtils.
 */
class StringUtils extends StringUtilsAlias
{

    /**
     * @param array  $data
     * @param string $subject
     *
     * @return string
     */
    public function strReplaceArray(array $data, string $subject): string
    {
        return str_ireplace(array_keys($data), array_values($data), $subject);
    }

    /**
     * Capitalize first letters and convert separators if needed.
     *
     * @param string       $str
     * @param string|array $sourceSeparator
     * @param string       $destinationSeparator
     *
     * @return string
     */
    public function upperCaseWords($str, $sourceSeparator = [], $destinationSeparator = '_'): string
    {
        $sourceSeparator = array_merge([',', '-', '.', '"', '_'], (array)$sourceSeparator);
        $sourceSeparator = array_map('strtolower', $sourceSeparator);

        $str = strtolower($str);

        $str = str_replace(' ', $destinationSeparator, ucwords(str_replace($sourceSeparator, ' ', $str)));

        return $this->trim($str);
    }

    /**
     * @param string $string
     *
     * @return string
     */
    public function trim($string): string
    {
        return trim(preg_replace('/\s{2,}/siu', ' ', $string));
    }
}
