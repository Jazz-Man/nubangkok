<?php

namespace Encomage\Customer\Plugin\Block\Widget;

use Magento\Customer\Block\Widget\Dob as Subject;

/**
 * Class Dob
 * @package Encomage\Customer\Plugin\Block\Widget
 */
class Dob
{
    /**
     * @param Subject $subject
     * @param $result
     * @return string
     */
    public function afterGetDateFormat(Subject $subject, $result)
    {

        switch ($result) {
            case 'M/d/Y':
                return 'mm/dd/Y';
            case 'd/M/Y':
                return 'dd/mm/Y';
            default:
                return $result;
        }


    }
}