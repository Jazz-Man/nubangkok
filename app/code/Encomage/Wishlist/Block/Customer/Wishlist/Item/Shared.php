<?php

namespace Encomage\Wishlist\Block\Customer\Wishlist\Item;

class Shared extends \Magento\Wishlist\Block\Customer\Wishlist\Item\Column
{

    private $helper;

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        \Encomage\Catalog\Helper\Data $helper,
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
        return $this->helper->getFacebookSharedLink($this->getItem()->getProduct());
    }

}