<?php
namespace Encomage\Catalog\Plugin\Block\Product\View;

use Magento\Catalog\Helper\Image;
use Magento\Framework\Data\Collection;
use Magento\Framework\Registry;

/**
 * Class Gallery
 *
 * @package Encomage\Catalog\Plugin\Block\Product\View
 */
class Gallery
{
    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $_imageHelper;
    
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;
    
    /**
     * Gallery constructor.
     *
     * @param \Magento\Catalog\Helper\Image $image
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        Image $image,
        Registry $registry
    ) {
        $this->_imageHelper = $image;
        $this->_coreRegistry = $registry;
    }
    
    /**
     * Retrieve collection of gallery images
     * 
     * @param \Magento\Catalog\Block\Product\View\Gallery $object
     * @param callable $proceed
     * @return mixed
     */
    public function aroundGetGalleryImages(\Magento\Catalog\Block\Product\View\Gallery $object, callable $proceed)
    {
        $product = $object->getProduct();
        $simpleProduct = $this->_coreRegistry->registry('simple_product');
        if ($simpleProduct === null || $product->getTypeId() !== 'configurable') {
            return $proceed();
        }

        $images = $simpleProduct->getMediaGalleryImages();
        if ($images instanceof Collection) {
            foreach ($images as $image) {
                /* @var \Magento\Framework\DataObject $image */
                $image->setData(
                    'small_image_url',
                    $this->_imageHelper->init($simpleProduct, 'product_page_image_small')
                        ->setImageFile($image->getFile())
                        ->getUrl()
                );
                $image->setData(
                    'medium_image_url',
                    $this->_imageHelper->init($simpleProduct, 'product_page_image_medium_no_frame')
                        ->setImageFile($image->getFile())
                        ->getUrl()
                );
                $image->setData(
                    'large_image_url',
                    $this->_imageHelper->init($simpleProduct, 'product_page_image_large_no_frame')
                        ->setImageFile($image->getFile())
                        ->getUrl()
                );
            }
        }

        return $images;
    }
}