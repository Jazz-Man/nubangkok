<?php

namespace Encomage\Catalog\Block\Product;

use Magento\Catalog\Block\Product\ImageFactory;
use Magento\Catalog\Helper\ImageFactory as HelperFactory;

class ImageBuilder extends \Magento\Catalog\Block\Product\ImageBuilder
{

    /**
     * ImageBuilder constructor.
     * @param HelperFactory $helperFactory
     * @param ImageFactory $imageFactory
     */
    public function __construct(HelperFactory $helperFactory, ImageFactory $imageFactory)
    {
        parent::__construct($helperFactory, $imageFactory);
    }

    /**
     * @return \Magento\Catalog\Block\Product\Image
     */
    public function create()
    {
        /** @var \Magento\Catalog\Helper\Image $helper */
        $helper = $this->helperFactory->create()
            ->init($this->product, $this->imageId);

        $template = $helper->getFrame()
            ? 'Magento_Catalog::product/image.phtml'
            : 'Magento_Catalog::product/image_with_borders.phtml';

        $imagesize = $helper->getResizedImageInfo();

        $data = [
            'data' => [
                'template' => $template,
                'image_url' => $helper->getUrl(),
                'width' => $helper->getWidth(),
                'height' => $helper->getHeight(),
                'label' => $helper->getLabel(),
                'ratio' => $this->getRatio($helper),
                'custom_attributes' => $this->getCustomAttributes(),
                'resized_image_width' => !empty($imagesize[0]) ? $imagesize[0] : $helper->getWidth(),
                'resized_image_height' => !empty($imagesize[1]) ? $imagesize[1] : $helper->getHeight(),
                'product' => $this->getProduct(),
                'new' => $this->getIsNew(),
            ]
        ];

        return $this->imageFactory->create($data);
    }

    /**
     * @return bool|\Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        $product = false;
        if (!empty($this->product)) {
            $product = $this->product;
        }
        return $product;
    }

    /**
     * @return bool
     */
    public function getIsNew()
    {
        $isNew = false;
        $toDay = strtotime("now");
        $product = $this->getProduct();
        if ($product) {
            $newsFromDate = strtotime($product->getNewsFromDate());
            $newsToDate = strtotime($product->getNewsToDate());
            if ($toDay <= $newsToDate && $toDay >= $newsFromDate) {
                $isNew = true;
            }
        }
        return $isNew;
    }


}