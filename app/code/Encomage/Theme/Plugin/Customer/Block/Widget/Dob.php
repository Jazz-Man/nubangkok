<?php

namespace Encomage\Theme\Plugin\Customer\Block\Widget;


class Dob
{

    public function afterGetHtmlExtraParams(\Magento\Customer\Block\Widget\Dob $subject, $result)
    {
        $result .= ' placeholder="' . __('Birthday') . ' DD/MM/YYYY"';
        return $result;
    }
}