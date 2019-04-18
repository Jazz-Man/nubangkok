<?php

namespace Encomage\Slider\Block\Widget;

use Magento\Widget\Block\BlockInterface;

class Slider extends \Encomage\Theme\Block\Slider implements BlockInterface
{

    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('Mageplaza_BannerSlider::slider.phtml');
    }

    public function getBannerId()
    {
        return $this->getData('slider');
    }
}