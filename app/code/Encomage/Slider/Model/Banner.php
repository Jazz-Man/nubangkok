<?php

namespace Encomage\Slider\Model;

use Magento\Framework\App\ObjectManager;
use Mageplaza\BannerSlider\Model\Banner as BannerAlias;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Banner
 *
 * @package Encomage\Slider\Model
 */
class Banner extends BannerAlias
{

    /**
     * @param bool $mobile
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBannerUrl($mobile = false)
    {
        $objectManager = ObjectManager::getInstance();
        $storeManager = $objectManager->get(StoreManagerInterface::class);
        $baseUrl = $storeManager->getStore()->getBaseUrl();
        return (!$mobile)
            ? $baseUrl . 'pub/media/mageplaza/bannerslider/banner/image' . $this->getUploadFile()
            : $baseUrl . 'pub/media/mageplaza/bannerslider/banner/image' . $this->getMobileUploadFile();

    }
}