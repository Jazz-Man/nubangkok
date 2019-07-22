<?php

namespace Encomage\Catalog\Block\Product\View;

use Encomage\Theme\Helper\Data;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Encomage\Theme\Block\Html\Page\FacebookShareLinkInterface;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Block\Product\View;
use Magento\Catalog\Helper\Product;
use Magento\Catalog\Model\ProductTypes\ConfigInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\StringUtils;

/**
 * Class Share
 *
 * @package Encomage\Catalog\Block\Product\View
 */
class Share extends View implements FacebookShareLinkInterface
{
    private $helper;

    /**
     * Share constructor.
     *
     * @param \Magento\Catalog\Block\Product\Context              $context
     * @param \Magento\Framework\Url\EncoderInterface             $urlEncoder
     * @param \Magento\Framework\Json\EncoderInterface            $jsonEncoder
     * @param \Magento\Framework\Stdlib\StringUtils               $string
     * @param \Magento\Catalog\Helper\Product                     $productHelper
     * @param \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig
     * @param \Magento\Framework\Locale\FormatInterface           $localeFormat
     * @param \Magento\Customer\Model\Session                     $customerSession
     * @param \Magento\Catalog\Api\ProductRepositoryInterface     $productRepository
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface   $priceCurrency
     * @param \Encomage\Theme\Helper\Data                         $helper
     * @param array                                               $data
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        EncoderInterface $jsonEncoder,
        StringUtils $string,
        Product $productHelper,
        ConfigInterface $productTypeConfig,
        FormatInterface $localeFormat,
        Session $customerSession,
        ProductRepositoryInterface $productRepository,
        PriceCurrencyInterface $priceCurrency,
        Data $helper,
        array $data = []
    )
    {
        parent::__construct(
            $context,
            $urlEncoder,
            $jsonEncoder,
            $string,
            $productHelper,
            $productTypeConfig,
            $localeFormat,
            $customerSession,
            $productRepository,
            $priceCurrency,
            $data
        );
        $this->helper = $helper;
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('Magento_Theme::html/page/share.phtml');
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->helper->getFacebookShareLink($this->getProduct()->getProductUrl());
    }

    /**
     * @return $this|\Magento\Catalog\Block\Product\View
     */
    protected function _prepareLayout()
    {
        return $this;
    }

    /**
     * @return mixed|string
     */
    public function getCssClass()
    {
        return $this->getData('css_class');
    }


    /**
     * @param string $cssClass
     *
     * @return $this|mixed
     */
    public function setCssClass(string $cssClass)
    {
        $this->setData('css_class', $cssClass);
        return $this;
    }
}