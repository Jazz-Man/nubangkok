<?php

namespace Encomage\ErpIntegration\Cron;

use Encomage\ErpIntegration\Helper\ApiClient;
use Encomage\ErpIntegration\Helper\Data;
use Encomage\ErpIntegration\Helper\ScrapCommandTrait;
use Exception;
use GuzzleHttp\Psr7\Response;
use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\CatalogInventory\Api\StockRegistryInterfaceFactory;
use Magento\ConfigurableProduct\Api\LinkManagementInterfaceFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as TypeConfigurableProduct;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Psr\Log\LoggerInterface;


/**
 * Class ImportProducts
 *
 * @package Encomage\ErpIntegration\Cron
 */
class ImportProducts
{
    use ScrapCommandTrait;

    /**
     * @var array
     */
    private $products_data = [[]];

    /**
     * @var ProductResource
     */
    private $productResource;

    /**
     * @var CategoryLinkManagementInterface
     */
    private $categoryLinkManagement;
    /**
     * @var TypeConfigurableProduct
     */
    private $typeConfigurableProduct;
    /**
     * @var StockRegistryInterfaceFactory
     */
    private $stockRegistryFactory;

    /**
     * @var LinkManagementInterfaceFactory
     */
    private $linkManagementFactory;

    /**
     * @var \Encomage\ErpIntegration\Helper\ApiClient
     */
    private $apiClient;

    /**
     * @var \Encomage\ErpIntegration\Helper\Data
     */
    private $helper;

    /**
     * @var int
     */
    private $color_option_id;
    /**
     * @var int
     */
    private $size_option_id;
    /**
     * @var array
     */
    private $configurableProductData = [];

    private $configurableProductCategity = [];
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * ImportProducts constructor.
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface              $scopeConfig
     * @param \Magento\Catalog\Model\ResourceModel\Product                    $productResource
     * @param \Magento\Catalog\Api\CategoryLinkManagementInterface            $categoryLinkManagement
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable    $typeConfigurableProduct
     * @param \Magento\CatalogInventory\Api\StockRegistryInterfaceFactory     $stockRegistryFactory
     * @param \Magento\ConfigurableProduct\Api\LinkManagementInterfaceFactory $linkManagementFactory
     * @param \Psr\Log\LoggerInterface                                        $logger
     * @param \Encomage\ErpIntegration\Helper\Data                            $helper
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ProductResource $productResource,
        CategoryLinkManagementInterface $categoryLinkManagement,
        TypeConfigurableProduct $typeConfigurableProduct,
        StockRegistryInterfaceFactory $stockRegistryFactory,
        LinkManagementInterfaceFactory $linkManagementFactory,
        LoggerInterface $logger,
        Data $helper
    ) {

        $this->productResource         = $productResource;
        $this->categoryLinkManagement  = $categoryLinkManagement;
        $this->typeConfigurableProduct = $typeConfigurableProduct;
        $this->stockRegistryFactory    = $stockRegistryFactory;
        $this->linkManagementFactory   = $linkManagementFactory;


        $this->apiClient = new ApiClient($scopeConfig);


        $this->helper = $helper;

        $this->color_option_id = $this->productResource->getAttribute('color')->getId();
        $this->size_option_id  = $this->productResource->getAttribute('size')->getId();
        $this->logger          = $logger;
    }

    /**
     * Write to system.log
     *
     * @return void
     */
    public function execute(): void
    {
        $this->logger->info('Start ERP Cron Import');


        $last_point = 'GetProductList';

        $query = [
            'Branchpricedisplay'    => 1,
            'CategoryDisplaySubCat' => 1,
            'Page'                  => 1,
        ];

        do {
            /** @var Response $response */
            $response = $this->apiClient->getData($last_point, $query);
        } while ($this->parseBody($response) && $query['Page']++);

        if ( ! empty($this->products_data)) {
            $this->products_data = array_merge(...$this->products_data);

            $this->dataProccesing($this->products_data);
        }

        $this->logger->info('Finish ERP Cron Import');

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


        if (null !== $exception) {
            $this->logger->error($exception->getMessage());
        }
    }


}