<?php


namespace Encomage\ErpIntegration\Helper;


use Magento\Framework\Stdlib\StringUtils as StringUtilsAlias;

/**
 * Class StringUtils
 *
 * @package Encomage\ErpIntegration\Helper
 */
class StringUtils extends StringUtilsAlias
{

    /**
     * Binary-safe variant of strSplit()
     * + option not to break words
     * + option to trim spaces (between each word)
     * + option to set character(s) (pcre pattern) to be considered as words separator
     *
     * @param string $value
     * @param int    $length
     * @param bool   $keepWords
     * @param bool   $trim
     * @param string $wordSeparatorRegex
     *
     * @return string[]
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function split($value, $length = 1, $keepWords = false, $trim = false, $wordSeparatorRegex = '\s')
    {
        $result = [];
        $strLen = $this->strlen($value);
        if ( ! $strLen || ! is_int($length) || $length <= 0) {
            return $result;
        }
        if ($trim) {
            $value = $this->trim($value);
        }
        // do a usual str_split, but safe for our encoding
        if ( ! $keepWords || $length < 2) {
            for ($offset = 0; $offset < $strLen; $offset += $length) {
                $result[] = $this->substr($value, $offset, $length);
            }
        } else {
            // split smartly, keeping words
            $split    = preg_split('/(' . $wordSeparatorRegex . '+)/siu', $value, null, PREG_SPLIT_DELIM_CAPTURE);
            $index    = 0;
            $space    = '';
            $spaceLen = 0;
            foreach ($split as $key => $part) {
                if ($trim) {
                    // ignore spaces (even keys)
                    if ($key % 2) {
                        continue;
                    }
                    $space    = ' ';
                    $spaceLen = 1;
                }
                if (empty($result[$index])) {
                    $currentLength  = 0;
                    $result[$index] = '';
                    $space          = '';
                    $spaceLen       = 0;
                } else {
                    $currentLength = $this->strlen($result[$index]);
                }
                $partLength = $this->strlen($part);
                // add part to current last element
                if ($currentLength + $spaceLen + $partLength <= $length) {
                    $result[$index] .= $space . $part;
                } elseif ($partLength <= $length) {
                    // add part to new element
                    $index++;
                    $result[$index] = $part;
                } else {
                    // break too long part recursively
                    foreach ($this->split($part, $length, false, $trim, $wordSeparatorRegex) as $subPart) {
                        $index++;
                        $result[$index] = $subPart;
                    }
                }
            }
        }
        // remove last element, if empty
        $count = count($result);
        if ($count && $result[$count - 1] === '') {
            unset($result[$count - 1]);
        }
        // remove first element, if empty
        if (isset($result[0]) && $result[0] === '') {
            array_shift($result);
        }

        return $result;
    }


    /**
     * @param array  $data
     * @param string $subject
     *
     * @return string
     */
    public function strReplaceArray(array $data, string $subject): string
    {
        return str_replace(array_keys($data), array_values($data), $subject);
    }

    /**
     * Capitalize first letters and convert separators if needed
     *
     * @param string       $str
     * @param string|array $sourceSeparator
     * @param string       $destinationSeparator
     *
     * @return string
     */
    public function upperCaseWords($str, $sourceSeparator = [], $destinationSeparator = '_')
    {

        $sourceSeparator = array_merge([',', '-', '.', '_'], (array)$sourceSeparator);
        $sourceSeparator = array_map('strtolower', $sourceSeparator);

        $str = strtolower($str);

        $str = str_replace(' ', $destinationSeparator, ucwords(str_replace($sourceSeparator, ' ', $str)));

        return $this->trim($str);
    }


    /**
     * @param $sku
     *
     * @return string
     */
    public function sanitizeSku($sku): string
    {

        if (function_exists('iconv')) {
            $sku = iconv('UTF-8', 'ASCII//TRANSLIT', $sku);
        }
        $sku = preg_replace("/[^a-zA-Z0-9\.\-\_]/", '', $this->trim($sku));

        $sku = preg_replace('/\s+/', '-', $sku);
        $sku = preg_replace('/(\-)\1+/', '$1', $sku);

        return $sku;

    }

    /**
     * @param string $string
     *
     * @return string
     */
    public function trim($string)
    {
        return trim(preg_replace('/\s{2,}/siu', ' ', $string));
    }

}