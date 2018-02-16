<?php

namespace Encomage\Customer\Model\Data;

class Customer extends \Magento\Customer\Model\Data\Customer
{
    public function getLineId()
    {
        return $this->getCustomAttribute('line_id') ?
            $this->getCustomAttribute('line_id')->getValue() : null;
    }

    public function getGenderLabel()
    {
        $genderCodeId = $this->getGender();
        if ($genderCodeId) {
            switch ((int)$genderCodeId) {
                case 1:
                    return "Male";
                case 2:
                    return "Female";
                case 3:
                    return "Not Specified";
            }
        }
        return null;
    }
}