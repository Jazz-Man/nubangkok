<?php

namespace ErpAPI\ErpAPICommand\Console\Command;

use Encomage\ErpIntegration\Helper\ApiClient;
use Encomage\ErpIntegration\Helper\CacheFile;
use Encomage\ErpIntegration\Helper\Data;
use Encomage\ErpIntegration\Helper\ScrapCommandTrait;
use GuzzleHttp\Psr7\Response;
use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\CatalogInventory\Api\StockRegistryInterfaceFactory;
use Magento\ConfigurableProduct\Api\LinkManagementInterfaceFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as TypeConfigurableProduct;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class HelloWorldCommand.
 */
class ScrapCommand extends Command
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
     * @var ObjectManagerInterface
     */
    private $objectManager;

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
     * @var \Encomage\ErpIntegration\Helper\CacheFile
     */
    private $cacheFile;
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
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    private $cliOutput;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var array
     */
    private $configurableProductData = [];

    /**
     * @var array
     */
    private $configurableProductCategory = [];

    private $askAboutShoeSize = [];
    /**
     * @var array
     */
    private $productNames = [];
    /**
     * @var \Symfony\Component\Console\Helper\Table
     */
    private $cliTable;

    /**
     * ScrapCommand constructor.
     *
     * @param ScopeConfigInterface                                            $scopeConfig
     * @param ProductResource                                                 $productResource
     * @param ObjectManagerInterface                                          $objectManager
     * @param CategoryLinkManagementInterface                                 $categoryLinkManagement
     * @param TypeConfigurableProduct                                         $typeConfigurableProduct
     * @param StockRegistryInterfaceFactory                                   $stockRegistryFactory
     * @param \Magento\ConfigurableProduct\Api\LinkManagementInterfaceFactory $linkManagementFactory
     * @param \Magento\Store\Model\StoreManagerInterface                      $storeManager
     * @param \Encomage\ErpIntegration\Helper\Data                            $helper
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ProductResource $productResource,
        ObjectManagerInterface $objectManager,
        CategoryLinkManagementInterface $categoryLinkManagement,
        TypeConfigurableProduct $typeConfigurableProduct,
        StockRegistryInterfaceFactory $stockRegistryFactory,
        LinkManagementInterfaceFactory $linkManagementFactory,
        StoreManagerInterface $storeManager,
        Data $helper
    ) {
        $this->productResource = $productResource;
        $this->objectManager = $objectManager;
        $this->categoryLinkManagement = $categoryLinkManagement;
        $this->typeConfigurableProduct = $typeConfigurableProduct;
        $this->stockRegistryFactory = $stockRegistryFactory;
        $this->linkManagementFactory = $linkManagementFactory;

        parent::__construct();

        $this->storeManager = $storeManager;

        $this->apiClient = new ApiClient($scopeConfig);
        $this->cacheFile = new CacheFile($objectManager);

        $this->helper = $helper;

        $this->color_option_id = $this->productResource->getAttribute('color')->getId();
        $this->size_option_id = $this->productResource->getAttribute('size')->getId();
    }

    protected function configure()
    {
        $this->setName('erpapi:scrap')->setDescription('So much hello world.');

        $state = $this->objectManager->get(State::class);

        try {
            $state->getAreaCode();
        } catch (LocalizedException $e) {
            $state->setAreaCode('adminhtml');
        }

        parent::configure();
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface                 $input
     * @param \Symfony\Component\Console\Output\ConsoleOutput|OutputInterface $output
     *
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->cliOutput = $output;

        $this->cliOutput->writeln('Hello World!');

        $this->cliTable = new Table($output);

        $this->storeManager->setCurrentStore(Store::DEFAULT_STORE_ID);

        $CacheFile = $this->cacheFile->getCacheFile();

        if ($CacheFile) {
            $this->dataProccesing($CacheFile);
        } else {
            $query = [
                'Branchpricedisplay' => 1,
                'CategoryDisplaySubCat' => 1,
                'Page' => 1,
            ];

            do {
                /** @var Response $response */
                $response = $this->apiClient->getData('GetProductList', $query);
            } while ($this->parseBody($response) && $query['Page']++);

            if (!empty($this->products_data)) {
                $this->products_data = array_merge(...$this->products_data);

                $this->cacheFile->saveCacheFile($this->products_data);

                $this->dataProccesing($this->products_data);
            }
        }

        dump($this->helper->testMemory());
    }
}
