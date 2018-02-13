<?php

namespace Encomage\Theme\Block;

use Magento\Framework\View\Element\Template\Context;
use Mageplaza\BetterSlider\Model\BannerFactory as BannerModelFactory;
use Mageplaza\BetterSlider\Model\SliderFactory as SliderModelFactory;

class Slider extends \Mageplaza\BetterSlider\Block\Slider
{
    protected $_json;

    public function __construct(
        Context $context,
        SliderModelFactory $sliderFactory,
        BannerModelFactory $bannerFactory,
        \Magento\Framework\Serialize\Serializer\Json $json
    )
    {
        parent::__construct($context, $sliderFactory, $bannerFactory);
        $this->_json = $json;
    }

    public function getConfig()
    {
        return $this->_json->serialize(
            [
                'loop' => (bool)$this->getLoop(),
                'dots' => (bool)$this->getDots(),
                'items' => ($this->getItems() && $this->getItems() > 1) ? $this->getItems() : 1,
                'nav' => (bool)$this->getNav(),
                'navText' => ['',''],
                'autoHeight' => (bool)$this->getAutoHeight(),
            ]
        );
    }
}