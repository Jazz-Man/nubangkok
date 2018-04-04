<?php

namespace Encomage\Slider\Model;

class Banner extends \Mageplaza\BetterSlider\Model\Banner
{

    public function getBannerUrl($mobile = false)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $baseUrl = $storeManager->getStore()->getBaseUrl();
        return (!$mobile)
            ? $baseUrl . 'pub/media/mageplaza/betterslider/banner/image' . $this->getUploadFile()
            : $baseUrl . 'pub/media/mageplaza/betterslider/banner/image' . $this->getMobileUploadFile();

    }
}