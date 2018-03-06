<?php

namespace Encomage\Wishlist\Block\Customer\Wishlist\Item;
use Encomage\Theme\Block\Html\Page\FacebookShareLinkInterface;

class Share extends \Magento\Wishlist\Block\Customer\Wishlist\Item\Column implements FacebookShareLinkInterface
{

    private $helper;

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        \Encomage\Theme\Helper\Data $helper,
        array $data = []
    )
    {
        parent::__construct($context, $httpContext, $data);
        $this->helper = $helper;
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->helper->getFacebookShareLink($this->getItem()->getProduct()->getProductUrl());
    }

    /**
     * @return string
     */
    public function getCssClass()
    {
        return $this->getData('css_class');
    }

    /**
     * @param string $cssClass
     * @return $this|mixed
     */
    public function setCssClass(string $cssClass)
    {
        $this->setData('css_class', $cssClass);
        return $this;
    }
}