<?php

namespace Encomage\ConfigurableProduct\Rewrite\Block\Product\Renderer;

use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Helper\Product as CatalogProduct;
use Magento\ConfigurableProduct\Helper\Data;
use Magento\ConfigurableProduct\Model\ConfigurableAttributeData;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\ArrayUtils;
use Magento\Swatches\Helper\Data as SwatchData;
use Magento\Swatches\Helper\Media;
use Magento\Swatches\Model\SwatchAttributesProvider;
use Magento\Framework\Locale\Format;

class Configurable extends \Magento\Swatches\Block\Product\Renderer\Configurable
{

    protected $_localeFormat;
    protected $_stockItemRepository;
    public function __construct(
        Context $context,
        ArrayUtils $arrayUtils,
        EncoderInterface $jsonEncoder,
        Data $helper,
        CatalogProduct $catalogProduct,
        CurrentCustomer $currentCustomer,
        PriceCurrencyInterface $priceCurrency,
        ConfigurableAttributeData $configurableAttributeData,
        SwatchData $swatchHelper,
        Media $swatchMediaHelper,
        array $data = [],
        SwatchAttributesProvider $swatchAttributesProvider = null,
        \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository,
        Format $localeFormat
    )
    {
        $this->_localeFormat = $localeFormat;
        $this->_stockItemRepository = $stockItemRepository;
        parent::__construct($context, $arrayUtils, $jsonEncoder, $helper, $catalogProduct, $currentCustomer, $priceCurrency, $configurableAttributeData, $swatchHelper, $swatchMediaHelper, $data, $swatchAttributesProvider);
    }

    public function getJsonConfig()
    {
        $store = $this->getCurrentStore();
        $currentProduct = $this->getProduct();
        $regularPrice = $currentProduct->getPriceInfo()->getPrice('regular_price');
        $finalPrice = $currentProduct->getPriceInfo()->getPrice('final_price');
        $options = $this->helper->getOptions($currentProduct, $this->getAllowProducts());
        $attributesData = $this->configurableAttributeData->getAttributesData($currentProduct, $options);

        $config = [
            'attributes' => $attributesData['attributes'],
            'template' => str_replace('%s', '<%- data.price %>', $store->getCurrentCurrency()->getOutputFormat()),
            'currencyFormat' => $store->getCurrentCurrency()->getOutputFormat(),
            'optionPrices' => $this->getOptionPrices(),
            'priceFormat' => $this->_localeFormat->getPriceFormat(),
            'prices' => [
                'oldPrice' => [
                    'amount' => $this->_localeFormat->getNumber($regularPrice->getAmount()->getValue()),
                ],
                'basePrice' => [
                    'amount' => $this->_localeFormat->getNumber($finalPrice->getAmount()->getBaseAmount()),
                ],
                'finalPrice' => [
                    'amount' => $this->_localeFormat->getNumber($finalPrice->getAmount()->getValue()),
                ],
            ],
            'productId' => $currentProduct->getId(),
            'chooseText' => __('Choose an Option...'),
            'images' => $this->getOptionImages(),
            'index' => isset($options['index']) ? $options['index'] : [],
            'stockStatus' => $this->getStockStatus(),
        ];

        if ($currentProduct->hasPreconfiguredValues() && !empty($attributesData['defaultValues'])) {
            $config['defaultValues'] = $attributesData['defaultValues'];
        }
        $config = array_merge($config, $this->_getAdditionalConfig());
        return $this->jsonEncoder->encode($config);
    }

    /**
     * @return array
     */
    protected function getStockStatus()
    {
        $stockStatus = [];
        $allProducts = $this->getProduct()->getTypeInstance()->getUsedProducts($this->getProduct(), null);
        foreach ($allProducts as $product) {
            if ($product->isSaleable()) {
                $item[$product->getColor()]['size'] = $product->getSize();
                $item[$product->getColor()]['stock'] = $this->_stockItemRepository->get($product->getID())->getIsInStock();
                $stockStatus[] = $item;
                unset ($item);
            }
        }
        return $stockStatus;
    }

}