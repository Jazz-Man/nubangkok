<?php

namespace Encomage\ConfigurableProduct\Rewrite\Block\Product\Renderer;

use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Helper\Product as CatalogProduct;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\ConfigurableProduct\Helper\Data;
use Magento\ConfigurableProduct\Model\ConfigurableAttributeData;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable\Variations\Prices;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\App\ObjectManager;
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
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Variations\Prices
     */
    private $_variationPrices;

    /**
     * Configurable constructor.
     *
     * @param \Magento\Catalog\Block\Product\Context                                              $context
     * @param \Magento\Framework\Stdlib\ArrayUtils                                                $arrayUtils
     * @param \Magento\Framework\Json\EncoderInterface                                            $jsonEncoder
     * @param \Magento\ConfigurableProduct\Helper\Data                                            $helper
     * @param \Magento\Catalog\Helper\Product                                                     $catalogProduct
     * @param \Magento\Customer\Helper\Session\CurrentCustomer                                    $currentCustomer
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface                                   $priceCurrency
     * @param \Magento\ConfigurableProduct\Model\ConfigurableAttributeData                        $configurableAttributeData
     * @param \Magento\Swatches\Helper\Data                                                       $swatchHelper
     * @param \Magento\Swatches\Helper\Media                                                      $swatchMediaHelper
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface                                $stockRegistry
     * @param \Magento\Framework\Locale\Format                                                    $localeFormat
     * @param array                                                                               $data
     * @param \Magento\Swatches\Model\SwatchAttributesProvider|null                               $swatchAttributesProvider
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Variations\Prices|null $variationPrices
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
        SwatchAttributesProvider $swatchAttributesProvider = null,
        Prices $variationPrices = null
    )
    {
        $this->_localeFormat = $localeFormat;
        $this->_stockRegistry = $stockRegistry;

        $this->_variationPrices = $variationPrices ?: ObjectManager::getInstance()->get(
            Prices::class
        );

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
        $options = $this->helper->getOptions($currentProduct, $this->getAllowProducts());
        $attributesData = $this->configurableAttributeData->getAttributesData($currentProduct, $options);

        $config = [
            'attributes' => $attributesData['attributes'],
            'template' => str_replace('%s', '<%- data.price %>', $store->getCurrentCurrency()->getOutputFormat()),
            'currencyFormat' => $store->getCurrentCurrency()->getOutputFormat(),
            'optionPrices' => $this->getOptionPrices(),
            'priceFormat' => $this->_localeFormat->getPriceFormat(),
            'prices' => $this->_variationPrices->getFormattedPrices($this->getProduct()->getPriceInfo()),
            'productId' => $currentProduct->getId(),
            'chooseText' => __('Choose an Option...'),
            'images' => $this->getOptionImages(),
            'index' => $options['index'] ?? [],
            'stockStatus' => $this->getStockStatus(),
        ];

        if ( !empty($attributesData['defaultValues']) && $currentProduct->hasPreconfiguredValues()) {
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

                $productColor = $product->getColor();

                $stockItem = $this->_stockRegistry->getStockItem($product->getId());
                $item[$productColor]['size'] = $product->getSize();
                $item[$productColor]['stock'] = $stockItem->getIsInStock();
                $item[$productColor]['qty'] = $stockItem->getQty();
                $stockStatus[] = $item;
                unset ($item);
            }
        }
        return $stockStatus;
    }

}