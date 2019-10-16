<?php


namespace Encomage\ErpIntegration\Helper;


use Exception;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type as TypeAlias;
use Magento\Catalog\Model\Product\Visibility;
use Magento\CatalogInventory\Model\Configuration;
use Magento\CatalogInventory\Model\Stock\Item as StockItemAlias;
use Magento\ConfigurableProduct\Model\LinkManagement;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as TypeConfigurableProduct;
use Magento\Store\Model\Store;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Console\Helper\TableStyle;

/**
 * Trait ScrapCommand
 *
 * @package Encomage\ErpIntegration\Helper
 * @property \Encomage\ErpIntegration\Helper\ApiClient $apiClient
 * @property \Encomage\ErpIntegration\Helper\Data $helper
 * @property \Symfony\Component\Console\Helper\Table $cliTable
 * @property int $color_option_id
 * @property int $size_option_id
 */
trait ScrapCommandTrait
{
    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return bool
     */
    private function parseBody(ResponseInterface $response): bool
    {
        $products_data = $this->apiClient->parseBody($response);

        if (!empty($products_data)) {
            $this->products_data[] = $products_data;

            return true;
        }

        return false;
    }

    /**
     * @param array $products_data
     */
    protected function dataProccesing(array $products_data): void
    {
        $data = $this->helper->getErpProductsObjects($products_data);

        foreach ($data as $datum) {
            if ($datum->isValid()) {
                $category_id = $this->getCategoryId($datum);

                $size = !empty($datum->getSize()) ? $this->helper->getSizeIdByName($datum->getSize()) : false;

                $color = $this->helper->getColorIdByName($datum->getColor());

                $poduct_props = [
                    'sku' => $datum->getBarCode(),
                    'name'=>"{$datum->getModel()} {$datum->getColor()} {$datum->getSize()}",
                    'size' => $size,
                    'color' => $color,
                    'weight' => $datum->getGrossWeight(),
                    'salesPrice' => $datum->getSalesPrice(),
                    'model' => $datum->getModel(),
                    'stockStatus' => $datum->getStockStatus(),
                    'unrestrictStock' => $datum->getUnrestrictStock(),
                    'isShoes' => $datum->isShoes(),
                    'category_id' => $category_id,
                ];


                $this->prepareSimpleProduct($poduct_props);
            }
        }

        $this->buildConfigurable();
    }

    /**
     * @param \Encomage\ErpIntegration\Model\Api\ErpProduct $erp_product
     *
     * @return bool|int
     */
    private function getCategoryId($erp_product)
    {
        $category_path = [
            $erp_product->getRootCategoryName(),
        ];

        if (!empty($erp_product->getSubCategoryName())) {
            $category_path[] = $erp_product->getSubCategoryName();
        }

        if (!empty($erp_product->getFormat()) && $erp_product->getFormat() !== $erp_product->getSubCategoryName()) {
            $category_path[] = $this->helper->sanitizeKey($erp_product->getFormat());
        }

        $category = implode('/', $category_path);

        $category = strtolower($category);

        return $this->helper->getCategoryByPath($category);
    }

    /**
     * @param array $data
     */
    private function prepareSimpleProduct(array $data): void
    {
        list(
            'name' => $name,
            'sku' => $sku,
            'color' => $color,
            'size' => $size,
            'weight' => $weight,
            'salesPrice' => $salesPrice,
            'model' => $model,
            'stockStatus' => $stockStatus,
            'unrestrictStock' => $unrestrictStock,
            'isShoes' => $isShoes,
            'category_id' => $category_id,
            ) = $data;

        $product = $this->_prepareProduct($sku, $name);

        $product->setPrice($salesPrice)->setWeight($weight)->setQuantityAndStockStatus([
            StockItemAlias::IS_IN_STOCK => $stockStatus,
            StockItemAlias::QTY => $unrestrictStock,
        ])->setColor($color)->setSize($size);

        $this->askAboutShoeSize[$model] = $isShoes;

        $this->_saveProduct($product);

        if (!empty($category_id)) {
            $this->categoryLinkManagement->assignProductToCategories($sku, [$category_id]);
        }

        try {
            /** @var \Magento\CatalogInventory\Model\Stock\Item $stockItem */
            $stockItem = $this->stockRegistryFactory->create()->getStockItem($product->getId());
            $stockItem->setProduct($product)
                      ->setStoreId(Store::DEFAULT_STORE_ID)
                      ->setIsInStock($stockStatus)
                      ->setQty($unrestrictStock)
                      ->setIsQtyDecimal(false);

            $stockItem->save();

            $this->printProductException('Save Product Stock Data', $product);
        } catch (Exception $e) {
            $this->printProductException('Save Product Stock Data', $product, 'error', $e);
        }

        $this->configurableProductCategory[$model] = $category_id;

        $has_parent = !empty($this->typeConfigurableProduct->getParentIdsByChild($product->getId()));

        if (!$has_parent) {
            $this->configurableProductData[$model][] = $product->getSku();
        }
    }

    protected function buildConfigurable(): void
    {
        if (!empty($this->configurableProductData)) {
            foreach ($this->configurableProductData as $model => $childrens) {
                $config_sku = $this->helper->generateProductSku($model);

                $categoty_id = !empty($this->configurableProductCategory[$model]) ? $this->configurableProductCategory[$model] : null;

                $product = $this->_prepareProduct($config_sku, $model, TypeConfigurableProduct::TYPE_CODE,
                    Visibility::VISIBILITY_BOTH);

                if (!empty($this->askAboutShoeSize[$model])) {
                    $product->setAskAboutShoeSize($this->askAboutShoeSize[$model]);
                }

                $this->typeConfigurableProduct->setUsedProductAttributeIds([
                    $this->color_option_id,
                    $this->size_option_id,
                ], $product);

                $configurableAttributesData = $this->typeConfigurableProduct->getConfigurableAttributesAsArray($product);

                $product->setCanSaveConfigurableAttributes(true)
                        ->setConfigurableAttributesData($configurableAttributesData)
                        ->setConfigurableProductsData([]);

                $this->_saveProduct($product);

                if (!empty($categoty_id)) {
                    $this->categoryLinkManagement->assignProductToCategories($config_sku, [$categoty_id]);
                }

                if (!empty($childrens)) {
                    foreach ($childrens as $child) {
                        $child_product = $this->helper->getProductBySky($child);

                        $has_parent = !empty($this->typeConfigurableProduct->getParentIdsByChild($child_product->getId()));

                        if (!$has_parent) {
                            /** @var LinkManagement $linkManagement */
                            $linkManagement = $this->linkManagementFactory->create();

                            try {
                                $linkManagement->addChild($product->getSku(), $child_product->getSku());

                                $this->printProductException('Save Configurable Relation', $product, 'info', null,
                                    $child);
                            } catch (Exception $e) {
                                $this->printProductException('Save Relation Exception', $product, 'error', $e, $child);
                            }
                        }
                    }
                }

                try {
                    /** @var \Magento\CatalogInventory\Model\Stock\Item $stockItem */
                    $stockItem = $this->stockRegistryFactory->create()->getStockItem($product->getId());
                    $stockItem->setProduct($product)
                              ->setIsInStock(true)
                              ->setStoreId(Store::DEFAULT_STORE_ID)
                              ->setStockStatusChangedAutomaticallyFlag(true);
                    $stockItem->save();
                } catch (Exception $e) {
                    $this->printProductException('Save Product Stock Data', $product, 'error', $e);
                }
            }
        }
    }

    /**
     * @param string $sku
     * @param string $name
     * @param string $type_id
     * @param int    $visibility
     *
     * @return \Magento\Catalog\Api\Data\ProductInterface|\Magento\Catalog\Model\Product
     */
    private function _prepareProduct(
        string $sku,
        string $name,
        $type_id = TypeAlias::TYPE_SIMPLE,
        $visibility = Visibility::VISIBILITY_NOT_VISIBLE
    ) {
        $product = $this->helper->getProductBySky($sku);

        $is_new = null === $product->getId();

        if ($is_new) {
            $product->setSku($sku);
        }

        $product->setName($name)
                ->setWebsiteIds([
                    Configuration::DEFAULT_WEBSITE_ID => Configuration::DEFAULT_WEBSITE_ID,
                ])
                ->setVisibility($visibility)
                ->setStatus(Status::STATUS_ENABLED)
                ->setStoreId(Store::DEFAULT_STORE_ID)
                ->setTypeId($type_id)
                ->setAttributeSetId($product->getDefaultAttributeSetId());

        return $product;
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface|\Magento\Catalog\Model\Product $product
     */
    private function _saveProduct($product): void
    {
        try {
            $this->productResource->save($product);

            $this->printProductException("Save {$product->getTypeId()} product", $product);
        } catch (Exception $e) {
            $this->printProductException("Save {$product->getTypeId()} product", $product, 'error', $e);
        }
    }

    /**
     * @param string                                                                    $sction
     * @param \Magento\Catalog\Api\Data\ProductInterface|\Magento\Catalog\Model\Product $product
     * @param string                                                                    $color_style
     * @param \Exception                                                                $exception
     * @param string                                                                    $child_sku
     */
    public function printProductException(
        string $sction,
        $product,
        string $color_style = 'info',
        Exception $exception = null,
        $child_sku = ''
    ): void {
        $tableStyle = new TableStyle();

        $tableStyle->setCellHeaderFormat("<{$color_style}>%s</{$color_style}>");
        $tableStyle->setDefaultCrossingChar("<{$color_style}>+</{$color_style}>");
        $tableStyle->setVerticalBorderChars("<{$color_style}>|</{$color_style}>");
        $tableStyle->setHorizontalBorderChars("<{$color_style}>-</{$color_style}>");

        $data = [
            'Action' => $sction,
            'Product SKU' => $product->getSku(),
            'Product Name' => $product->getName(),
            'Product Type' => $product->getTypeId(),
            'Store ID' => $product->getStoreId(),
        ];

        if (!empty($child_sku)) {
            $data['Child SKU'] = $child_sku;
        }

        if (null !== $exception) {
            $data['Exception'] = $exception->getMessage();
        }

        $headers = array_keys($data);

        $this->cliTable->setStyle($tableStyle)->setHeaders($headers)->setRows([$data]);
        $this->cliTable->render();
    }

}