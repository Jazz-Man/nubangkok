<?php

namespace Encomage\Theme\Block;

use Magento\Framework\View\Element\Template\Context;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Mageplaza\BannerSlider\Helper\Data as bannerHelper;
use Magento\Framework\Serialize\Serializer\Json;
class Slider extends \Mageplaza\BannerSlider\Block\Slider
{
    protected $_json;

    public function __construct(
        Context $context,
        bannerHelper $helperData, 
        CustomerRepositoryInterface $customerRepository, 
        DateTime $dateTime, 
        FilterProvider $filterProvider,
        Json $json,
        array $data = []
    ) {
        parent::__construct($context, $helperData, $customerRepository, $dateTime, $filterProvider, $data);
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