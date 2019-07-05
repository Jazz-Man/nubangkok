<?php

namespace ErpAPI\ErpAPICommand\Console\Command;

use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\CatalogInventory\Api\StockRegistryInterfaceFactory;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\ConfigurableProduct\Api\LinkManagementInterfaceFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as TypeConfigurableProduct;
use Magento\Eav\Api\AttributeOptionManagementInterface;
use Magento\Eav\Model\AttributeRepository;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Eav\Model\Entity\Attribute\OptionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\ObjectManagerInterface;
use Symfony\Component\Console\Command\Command;
use GuzzleHttp\Client;
use Magento\UrlRewrite\Model\UrlPersistInterface;

/**
 * Class AbstractScrapCommand.
 */
class AbstractScrapCommand extends Command
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category
     */
    private $categoryResource;
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    private $productResource;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    private $productFactory;
    /**
     * @var \Magento\Eav\Model\Entity\Attribute
     */
    private $entityAttribute;
    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;
    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    private $directoryList;
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
     * @var \Magento\Eav\Model\Entity\Attribute\OptionFactory
     */
    private $optionFactory;
    /**
     * @var \Magento\Eav\Api\AttributeOptionManagementInterface
     */
    private $attributeOptionManager;
    /**
     * @var \Magento\Eav\Model\AttributeRepository
     */
    private $attributeRepository;
    /**
     * @var array
     */
    private $_attributesOptions;
    /**
     * @var \Magento\Framework\App\State
     */
    private $state;
    /**
     * @var Client
     */
    private $client;
    /**
     * @var \Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator
     */
    private $productUrlRewriteGenerator;


    public function __construct(
        \Magento\Framework\App\State $state,
        ScopeConfigInterface $scopeConfig,
        CategoryResource $categoryResource,
        ProductResource $productResource,
        ObjectManagerInterface $objectManager,
        ProductFactory $productFactory,
        Attribute $entityAttribute,
        Filesystem $filesystem,
        DirectoryList $directoryList,
        CategoryLinkManagementInterface $categoryLinkManagement,
        TypeConfigurableProduct $typeConfigurableProduct,
        StockRegistryInterfaceFactory $stockRegistryFactory,
        LinkManagementInterfaceFactory $linkManagementFactory,
        OptionFactory $optionFactory,
        AttributeOptionManagementInterface $attributeOptionManager,
        AttributeRepository $attributeRepository,
        ProductUrlRewriteGenerator $productUrlRewriteGenerator
    ) {
        parent::__construct();
        $this->scopeConfig = $scopeConfig;
        $this->categoryResource = $categoryResource;
        $this->productResource = $productResource;
        $this->objectManager = $objectManager;
        $this->productFactory = $productFactory;
        $this->entityAttribute = $entityAttribute;
        $this->filesystem = $filesystem;
        $this->directoryList = $directoryList;
        $this->categoryLinkManagement = $categoryLinkManagement;
        $this->typeConfigurableProduct = $typeConfigurableProduct;
        $this->stockRegistryFactory = $stockRegistryFactory;
        $this->linkManagementFactory = $linkManagementFactory;
        $this->optionFactory = $optionFactory;
        $this->attributeOptionManager = $attributeOptionManager;
        $this->attributeRepository = $attributeRepository;

        $this->_attributesOptions['color'] = $entityAttribute
            ->loadByCode('catalog_product', 'color')
            ->getSource()
            ->getAllOptions(false, true);

        $this->_attributesOptions['size'] = $entityAttribute
            ->loadByCode('catalog_product', 'size')
            ->getSource()
            ->getAllOptions(false, true);

        $this->state = $state;

        $this->productUrlRewriteGenerator = $productUrlRewriteGenerator;
    }

    protected function configure()
    {
        $this->setName('erpapi:scrap')
             ->setDescription('So much hello world.');

        try {
            $this->state->getAreaCode();
        } catch (LocalizedException $e) {
            $this->state->setAreaCode('adminhtml');
        }

        $this->_login = $this->scopeConfig->getValue('erp_etoday_settings/erp_authorization/login');
        $this->_password = $this->scopeConfig->getValue('erp_etoday_settings/erp_authorization/password');
        $this->_host_name = $this->scopeConfig->getValue('erp_etoday_settings/erp_authorization/host_name');
        $this->_compcode = $this->scopeConfig->getValue('erp_etoday_settings/erp_authorization/compcode');
        $this->_enabled_test_mode = (bool) $this->scopeConfig->getValue('erp_etoday_settings/erp_authorization/enabled_test_mode');
        $this->_warehouse_code = $this->scopeConfig->getValue('erp_etoday_settings/erp_authorization/warehouse_code');
        $this->_color_code = $this->scopeConfig->getValue('erp_etoday_settings/color_settings/color_code');
        $this->_bags_codes = $this->scopeConfig->getValue('erp_etoday_settings/category_type_bags/bags_codes');
        $this->_shoe_codes = $this->scopeConfig->getValue('erp_etoday_settings/category_type_shoe/shoe_codes');
        $this->_categories_codes = $this->scopeConfig->getValue('erp_etoday_settings/categories/categories_codes');

        $this->client = new Client([
            'timeout' => 30,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);

        parent::configure();
    }
}
