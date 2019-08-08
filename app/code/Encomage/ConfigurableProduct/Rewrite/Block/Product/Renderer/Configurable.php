<?php

namespace Encomage\ConfigurableProduct\Rewrite\Block\Product\Renderer;

use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Helper\Product as CatalogProduct;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\ConfigurableProduct\Helper\Data;
use Magento\ConfigurableProduct\Model\ConfigurableAttributeData;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\ArrayUtils;
use Magento\Swatches\Block\Product\Renderer\Configurable as ConfigurableAlias;
use Magento\Swatches\Helper\Data as SwatchData;
use Magento\Swatches\Helper\Media;
use Magento\Swatches\Model\SwatchAttributesProvider;
use Magento\Framework\Locale\Format;

/**
 * Class Configurable
 *
 * @package Encomage\ConfigurableProduct\Rewrite\Block\Product\Renderer
 */
class Configurable extends ConfigurableAlias
{

    protected $_localeFormat;
    protected $_stockRegistry;

    /**
     * Configurable constructor.
     *
     * @param \Magento\Catalog\Block\Product\Context                       $context
     * @param \Magento\Framework\Stdlib\ArrayUtils                         $arrayUtils
     * @param \Magento\Framework\Json\EncoderInterface                     $jsonEncoder
     * @param \Magento\ConfigurableProduct\Helper\Data                     $helper
     * @param \Magento\Catalog\Helper\Product                              $catalogProduct
     * @param \Magento\Customer\Helper\Session\CurrentCustomer             $currentCustomer
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface            $priceCurrency
     * @param \Magento\ConfigurableProduct\Model\ConfigurableAttributeData $configurableAttributeData
     * @param \Magento\Swatches\Helper\Data                                $swatchHelper
     * @param \Magento\Swatches\Helper\Media                               $swatchMediaHelper
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface         $stockRegistry
     * @param \Magento\Framework\Locale\Format                             $localeFormat
     * @param array                                                        $data
     * @param \Magento\Swatches\Model\SwatchAttributesProvider|null        $swatchAttributesProvider
     */
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
        StockRegistryInterface $stockRegistry,
        Format $localeFormat,
        array $data = [],
        SwatchAttributesProvider $swatchAttributesProvider = null
    )
    {
        $this->_localeFormat = $localeFormat;
        $this->_stockRegistry = $stockRegistry;
        parent::__construct($context, $arrayUtils, $jsonEncoder, $helper, $catalogProduct, $currentCustomer, $priceCurrency, $configurableAttributeData, $swatchHelper, $swatchMediaHelper, $data, $swatchAttributesProvider);
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
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
                $stockItem = $this->_stockRegistry->getStockItem($product->getId());
                $item[$product->getColor()]['size'] = $product->getSize();
                $item[$product->getColor()]['stock'] = $stockItem->getIsInStock();
                $item[$product->getColor()]['qty'] = $stockItem->getQty();
                $stockStatus[] = $item;
                unset ($item);
            }
        }
        return $stockStatus;
    }

}