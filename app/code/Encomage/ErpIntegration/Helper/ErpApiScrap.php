<?php


namespace Encomage\ErpIntegration\Helper;


use Encomage\ErpIntegration\Logger\Logger;
use Exception;
use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type as TypeAlias;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\CatalogInventory\Api\StockRegistryInterfaceFactory;
use Magento\CatalogInventory\Model\Configuration;
use Magento\ConfigurableProduct\Api\LinkManagementInterfaceFactory;
use Magento\ConfigurableProduct\Model\LinkManagement;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as TypeConfigurableProduct;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\Store;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableStyle;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ErpApiScrap
 *
 * @package Encomage\ErpIntegration\Helper
 */
class ErpApiScrap extends AbstractHelper
{

    /**
     * @var array
     */
    public $getProductListQuery = [
        'branchpricedisplay' => 1,
        'Page'               => 1,
    ];
    /**
     * @var string
     */
    public $getProductListPoint = 'GetProductList';
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    private $productResource;
    /**
     * @var \Magento\Catalog\Api\CategoryLinkManagementInterface
     */
    private $categoryLinkManagement;
    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    private $typeConfigurableProduct;
    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterfaceFactory
     */
    private $stockRegistryFactory;
    /**
     * @var \Magento\ConfigurableProduct\Api\LinkManagementInterfaceFactory
     */
    private $linkManagementFactory;
    /**
     * @var \Encomage\ErpIntegration\Logger\Logger
     */
    private $logger;
    /**
     * @var \Encomage\ErpIntegration\Helper\ErpApiClient
     */
    private $erpApiClient;
    /**
     * @var \Encomage\ErpIntegration\Helper\Data
     */
    private $helper;
    /**
     * @var integer
     */
    private $color_option_id;

    //    public $getProductListPoint = 'getproductlist';
    /**
     * @var integer
     */
    private $size_option_id;
    /**
     * @var array
     */
    private $products_data = [[]];
    /**
     * @var array
     */
    private $configurableProductCategory = [];
    /**
     * @var array
     */
    private $askAboutShoeSize = [];
    /**
     * @var array
     */
    private $configurableProductData = [];
    /**
     * @var \Symfony\Component\Console\Output\ConsoleOutput|OutputInterface
     */
    private $cliOutput;

    /**
     * @param Context                                                         $context
     * @param \Magento\Catalog\Model\ResourceModel\Product                    $productResource
     * @param \Magento\Catalog\Api\CategoryLinkManagementInterface            $categoryLinkManagement
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable    $typeConfigurableProduct
     * @param \Magento\CatalogInventory\Api\StockRegistryInterfaceFactory     $stockRegistryFactory
     * @param \Magento\ConfigurableProduct\Api\LinkManagementInterfaceFactory $linkManagementFactory
     * @param \Encomage\ErpIntegration\Logger\Logger                          $logger
     * @param \Encomage\ErpIntegration\Helper\ErpApiClient                    $erpApiClient
     * @param \Encomage\ErpIntegration\Helper\Data                            $helper
     */
    public function __construct(
        Context $context,
        ProductResource $productResource,
        CategoryLinkManagementInterface $categoryLinkManagement,
        TypeConfigurableProduct $typeConfigurableProduct,
        StockRegistryInterfaceFactory $stockRegistryFactory,
        LinkManagementInterfaceFactory $linkManagementFactory,
        Logger $logger,
        ErpApiClient $erpApiClient,
        Data $helper
    ) {
        parent::__construct($context);

        $this->productResource         = $productResource;
        $this->categoryLinkManagement  = $categoryLinkManagement;
        $this->typeConfigurableProduct = $typeConfigurableProduct;
        $this->stockRegistryFactory    = $stockRegistryFactory;
        $this->linkManagementFactory   = $linkManagementFactory;
        $this->logger                  = $logger;
        $this->erpApiClient            = $erpApiClient;
        $this->helper                  = $helper;

        $this->color_option_id = $this->productResource->getAttribute('color')->getId();
        $this->size_option_id  = $this->productResource->getAttribute('size')->getId();
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return bool
     */
    public function parseBody(ResponseInterface $response): bool
    {
        $products_data = $this->erpApiClient->parseBody($response);

        if ( ! empty($products_data)) {
            $this->products_data[] = $products_data;

            return true;
        }

        return false;
    }

    /**
     * @param array $products_data
     */
    public function dataProccesing(array $products_data = []): void
    {
        if (empty($products_data)) {
            $products_data = $this->getProductsData();
        }

        if ( ! empty($products_data)) {
            $data = $this->helper->getErpProductsObjects($products_data);

            foreach ($data as $datum) {
                if ($datum->isValid()) {
                    $category_id = $this->getCategoryId($datum);

                    $size = ! empty($datum->getSize()) ? $this->helper->getSizeIdByName($datum->getSize()) : false;

                    $color = $this->helper->getColorIdByName($datum->getColor());

                    $poduct_props = [
                        'sku'             => $datum->getBarCode(),
                        'name'            => "{$datum->getModel()} {$datum->getColor()} {$datum->getSize()}",
                        'size'            => $size,
                        'color'           => $color,
                        'weight'          => $datum->getGrossWeight(),
                        'salesPrice'      => $datum->getSalesPrice(),
                        'model'           => $datum->getModel(),
                        'stockStatus'     => $datum->getStockStatus(),
                        'unrestrictStock' => $datum->getUnrestrictStock(),
                        'isShoes'         => $datum->isShoes(),
                        'category_id'     => $category_id,
                    ];

                    $this->prepareSimpleProduct($poduct_props);
                }
            }

            $this->buildConfigurable();
        }
    }

    /**
     * @return array
     */
    public function getProductsData(): array
    {
        return $this->products_data;
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

        if ( ! empty($erp_product->getSubCategoryName())) {
            $category_path[] = $erp_product->getSubCategoryName();
        }

        if ( ! empty($erp_product->getFormat()) && $erp_product->getFormat() !== $erp_product->getSubCategoryName()) {
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
        list('name' => $name, 'sku' => $sku, 'color' => $color, 'size' => $size, 'weight' => $weight, 'salesPrice' => $salesPrice, 'model' => $model, 'stockStatus' => $stockStatus, 'unrestrictStock' => $unrestrictStock, 'isShoes' => $isShoes, 'category_id' => $category_id) = $data;

        $product = $this->_prepareProduct($sku, $name);

        $product->setPrice($salesPrice)->setWeight($weight)->setColor($color)->setSize($size);

        $this->askAboutShoeSize[$model] = $isShoes;

        $this->_saveProduct($product);

        if ( ! empty($category_id)) {
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

            if ($stockItem->hasDataChanges()) {
                $stockItem->save();

                $this->printProductException('Save Product Stock Data', $product);
            }
        } catch (Exception $e) {
            $this->printProductException('Save Product Stock Data', $product, 'error', $e);
        }

        $this->configurableProductCategory[$model] = $category_id;

        $has_parent = ! empty($this->typeConfigurableProduct->getParentIdsByChild($product->getId()));

        if ( ! $has_parent) {
            $this->configurableProductData[$model][] = $product->getSku();
        }
    }

    protected function buildConfigurable(): void
    {
        if ( ! empty($this->configurableProductData)) {
            foreach ($this->configurableProductData as $model => $childrens) {
                $config_sku = $this->helper->generateProductSku($model);

                $categoty_id = ! empty($this->configurableProductCategory[$model]) ? $this->configurableProductCategory[$model] : null;

                $product = $this->_prepareProduct($config_sku, $model, TypeConfigurableProduct::TYPE_CODE,
                    Visibility::VISIBILITY_BOTH);

                if ( ! empty($this->askAboutShoeSize[$model])) {
                    $product->setAskAboutShoeSize($this->askAboutShoeSize[$model]);
                }


                $this->typeConfigurableProduct->setUsedProductAttributes($product, [
                    $this->color_option_id,
                    $this->size_option_id,
                ]);


                $configurableAttributesData = $this->typeConfigurableProduct->getConfigurableAttributesAsArray($product);

                $product->setCanSaveConfigurableAttributes(true)
                        ->setConfigurableAttributesData($configurableAttributesData)
                        ->setConfigurableProductsData([]);

                $this->_saveProduct($product);

                if ( ! empty($categoty_id)) {
                    $this->categoryLinkManagement->assignProductToCategories($config_sku, [$categoty_id]);
                }

                if ( ! empty($childrens)) {
                    foreach ($childrens as $child) {
                        $child_product = $this->helper->getProductBySky($child);

                        $has_parent = ! empty($this->typeConfigurableProduct->getParentIdsByChild($child_product->getId()));

                        if ( ! $has_parent) {
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
            $product->setName($name)
                    ->setWebsiteIds([
                        Configuration::DEFAULT_WEBSITE_ID => Configuration::DEFAULT_WEBSITE_ID,
                    ])
                    ->setVisibility($visibility)
                    ->setStatus(Status::STATUS_ENABLED)
                    ->setStoreId(Store::DEFAULT_STORE_ID)
                    ->setTypeId($type_id)
                    ->setAttributeSetId($product->getDefaultAttributeSetId());
        }

        return $product;
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface|\Magento\Catalog\Model\Product $product
     */
    private function _saveProduct($product): void
    {

        if ($product->hasDataChanges()) {
            try {
                $this->productResource->save($product);

                $this->printProductException("Save {$product->getTypeId()} product", $product);
            } catch (Exception $e) {
                $this->printProductException("Save {$product->getTypeId()} product", $product, 'error', $e);
            }
        } else {
            dump($product->hasDataChanges());
        }
    }

    /**
     * @param string                                                                    $action
     * @param \Magento\Catalog\Api\Data\ProductInterface|\Magento\Catalog\Model\Product $product
     * @param string                                                                    $color_style
     * @param \Exception                                                                $exception
     * @param string                                                                    $child_sku
     */
    public function printProductException(
        string $action,
        $product,
        string $color_style = 'info',
        Exception $exception = null,
        $child_sku = ''
    ): void {


        $data = [
            'Action'       => $action,
            'Product SKU'  => $product->getSku(),
            'Product Name' => $product->getName(),
            'Product Type' => $product->getTypeId(),
            'Store ID'     => $product->getStoreId(),
        ];

        if ( ! empty($child_sku)) {
            $data['Child SKU'] = $child_sku;
        }

        if (null !== $exception) {
            $data['Exception'] = $exception->getMessage();

            $this->logger->error($action, $data);
        }


        if ($this->getCliOutput()) {

            $cliTable = new Table($this->getCliOutput());

            $tableStyle = new TableStyle();

            $tableStyle->setCellHeaderFormat("<{$color_style}>%s</{$color_style}>");
            $tableStyle->setDefaultCrossingChar("<{$color_style}>+</{$color_style}>");
            $tableStyle->setVerticalBorderChars("<{$color_style}>|</{$color_style}>");
            $tableStyle->setHorizontalBorderChars("<{$color_style}>-</{$color_style}>");

            $headers = array_keys($data);

            $cliTable->setStyle($tableStyle)->setHeaders($headers)->setRows([$data]);
            $cliTable->render();
        }
    }

    /**
     * @return \Symfony\Component\Console\Output\ConsoleOutput|\Symfony\Component\Console\Output\OutputInterface
     */
    public function getCliOutput()
    {
        return $this->cliOutput;
    }

    /**
     * @param \Symfony\Component\Console\Output\ConsoleOutput|\Symfony\Component\Console\Output\OutputInterface $cliOutput
     */
    public function setCliOutput($cliOutput): void
    {
        $this->cliOutput = $cliOutput;
    }

}