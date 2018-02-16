<?php

namespace Encomage\Catalog\Block\Product\View;

use Magento\Catalog\Api\ProductRepositoryInterface;

class Shared extends \Magento\Catalog\Block\Product\View
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
        \Encomage\Catalog\Helper\Data $helper,
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
        $this->setTemplate('Magento_Catalog::product/view/shared.phtml');
    }

    public function getLink()
    {
        return $this->helper->getFacebookSharedLink($this->getProduct());
    }

    protected function _prepareLayout()
    {
        return $this;
    }
}