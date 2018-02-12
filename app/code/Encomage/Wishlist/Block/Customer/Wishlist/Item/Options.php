<?php

namespace Encomage\Wishlist\Block\Customer\Wishlist\Item;

class Options extends \Magento\Wishlist\Block\Customer\Wishlist\Item\Options
{

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Catalog\Helper\Product\ConfigurationPool $helperPool,
        array $data = [])
    {
        parent::__construct(
            $context,
            $httpContext,
            $helperPool,
            $data
        );
    }

    /**
     * @return array
     */
    public function getSizes()
    {
        $sizes = [];
        foreach ($this->getConfiguredOptions() as $value) {
            if ($value['label'] == 'Size') {
                $sizes = $value;
                break 1;
            }
        }
        return $sizes;
    }
}