<?php
namespace Encomage\Customer\Data\Form\Filter;

use Magento\Framework\Stdlib\DateTime;

class Date extends \Magento\Framework\Data\Form\Filter\Date
{
    /**
     * @param string $value
     * @return array|string
     */
    public function outputFilter($value)
    {
        $filterInput = new \Zend_Filter_LocalizedToNormalized(
            ['date_format' => DateTime::DATE_INTERNAL_FORMAT, 'locale' => $this->localeResolver->getLocale()]
        );
        $filterInternal = new \Zend_Filter_NormalizedToLocalized(
            ['date_format' => $this->_dateFormat, 'locale' => $this->localeResolver->getLocale()]
        );

        $value = $filterInput->filter($value);
        $value = $filterInternal->filter($value);
        if (is_string($value)) {
            $result = explode(',', $value);
            if (count($result) > 1) {
                $value = '';
                foreach ($result as $date) {
                    $value .= $date;
                }
            }
        }
        return $value;
    }
}