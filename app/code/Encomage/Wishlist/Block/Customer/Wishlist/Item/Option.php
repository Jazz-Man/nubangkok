<?php

namespace Encomage\Wishlist\Block\Customer\Wishlist\Item;

class Option extends \Magento\Wishlist\Block\Customer\Wishlist\Item\Options
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('Magento_Wishlist::item/column/option.phtml');
    }

    public function getOption()
    {
        $options = [];
        if ($this->hasData('product_att')) {
            foreach ($this->getConfiguredOptions() as $value) {
                if ($value['label'] == $this->getData('product_att')) {
                    $options = $value;
                    break;
                }
            }
        }
        return $options;
    }

    public function getCssClass()
    {
        $cssClass = '';
        if ($this->hasData('product_att')) {
            $cssClass .= mb_strtolower($this->getData('product_att'));
        }
        if ($this->hasData('css_class')) {
            $cssClass .= ' ' . $this->getData('css_class');
        }
        return $cssClass;
    }
}