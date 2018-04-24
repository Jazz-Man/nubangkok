<?php

namespace Encomage\Catalog\Block\Product\View;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Encomage\Theme\Block\Html\Page\FacebookShareLinkInterface;

class Share extends \Magento\Catalog\Block\Product\View implements FacebookShareLinkInterface
{
    private $helper;

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Customer\Model\Session $customerSession,
        ProductRepositoryInterface $productRepository,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Encomage\Theme\Helper\Data $helper,
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

    public function getLink()
    {
        return $this->helper->getFacebookShareLink($this->getProduct()->getProductUrl());
    }

    protected function _prepareLayout()
    {
        return $this;
    }

    public function getCssClass()
    {
        return $this->getData('css_class');
    }


    public function setCssClass(string $cssClass)
    {
        $this->setData('css_class', $cssClass);
        return $this;
    }
}