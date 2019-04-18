<?php

namespace Encomage\Slider\Model;

class Banner extends \Mageplaza\BannerSlider\Model\Banner
{

    public function getBannerUrl($mobile = false)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $baseUrl = $storeManager->getStore()->getBaseUrl();
        return (!$mobile)
            ? $baseUrl . 'pub/media/mageplaza/bannerslider/banner/image' . $this->getUploadFile()
            : $baseUrl . 'pub/media/mageplaza/bannerslider/banner/image' . $this->getMobileUploadFile();

    }
}