<?php

namespace Encomage\Slider\Block\Widget;

use Encomage\Theme\Block\Slider as SliderAlias;
use Magento\Widget\Block\BlockInterface;

/**
 * Class Slider
 *
 * @package Encomage\Slider\Block\Widget
 */
class Slider extends SliderAlias implements BlockInterface
{

    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('Mageplaza_BannerSlider::slider.phtml');
    }

    /**
     * @return mixed
     */
    public function getBannerId()
    {
        return $this->getData('slider');
    }
}