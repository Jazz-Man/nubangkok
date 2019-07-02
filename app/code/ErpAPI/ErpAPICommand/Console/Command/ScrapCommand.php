<?php

namespace ErpAPI\ErpAPICommand\Console\Command;

use ErpAPI\ErpAPICommand\Model\Erp\ErpProduct;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\StringUtils;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class HelloWorldCommand.
 */
class ScrapCommand extends Command
{

    /**
     * @var ScopeConfigInterface
     */
    private $_config;

    /**
     * @var string
     */
    private $_login;
    /**
     * @var string
     */
    private $_password;
    /**
     * @var string
     */
    private $_host_name;
    /**
     * @var string
     */
    private $_categories_codes;
    /**
     * @var string
     */
    private $_shoe_codes;
    /**
     * @var string
     */
    private $_bags_codes;
    /**
     * @var string
     */
    private $_color_code;
    /**
     * @var string
     */
    private $_warehouse_code;
    /**
     * @var bool
     */
    private $_enabled_test_mode;
    /**
     * @var string
     */
    private $_compcode;
    /**
     * @var Client
     */
    private $request;
    private $products_data = [[]];

    /**
     * @var CategoryResource
     */
    private $categoryResource;

    /**
     * @var ProductResource
     */
    private $productResource;

    /**
     * @var array
     */
    private $all_categories;
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    private $productFactory;
    /**
     * @var StringUtils
     */
    private $string;

    /**
     * ScrapCommand constructor.
     *
     * @param ScopeConfigInterface                  $scopeConfig
     * @param CategoryResource                      $categoryResource
     * @param ProductResource                       $productResource
     * @param ObjectManagerInterface                $objectManager
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param ProductRepositoryInterface            $productRepository
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        CategoryResource $categoryResource,
        ProductResource $productResource,
        ObjectManagerInterface $objectManager,
        ProductFactory $productFactory,
        ProductRepositoryInterface $productRepository,
        StringUtils $string
    ) {
        $this->_config = $scopeConfig;
        $this->categoryResource = $categoryResource;
        $this->productResource = $productResource;
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->objectManager = $objectManager;
        $this->string = $string;
        parent::__construct();
    }

    /**
     * @return string
     */
    public function getHostName(): string
    {
        return $this->_host_name;
    }

    /**
     * @return array|mixed
     */
    public function getCategoriesCodes()
    {
        try {
            $categories_data = \GuzzleHttp\json_decode($this->_categories_codes, true);
        } catch (\InvalidArgumentException $e) {
            $categories_data = [];
        }

        return $categories_data;
    }

    /**
     * @return array|mixed
     */
    public function getShoeCodes()
    {
        try {
            $shoe_codes_data = \GuzzleHttp\json_decode($this->_shoe_codes, true);
        } catch (\InvalidArgumentException $e) {
            $shoe_codes_data = [];
        }

        return $shoe_codes_data;
    }

    /**
     * @return array|mixed
     */
    public function getBagsCodes()
    {
        try {
            $bags_codes_data = \GuzzleHttp\json_decode($this->_bags_codes, true);
        } catch (\InvalidArgumentException $e) {
            $bags_codes_data = [];
        }

        return $bags_codes_data;
    }

    /**
     * @return string
     */
    public function getColorCode(): string
    {
        return $this->_color_code;
    }

    protected function configure()
    {
        $this->setName('erpapi:scrap')->setDescription('So much hello world.');

        $state = $this->objectManager->get(State::class);
        $state->setAreaCode('crontab');

        $this->_login = $this->_config->getValue('erp_etoday_settings/erp_authorization/login');
        $this->_password = $this->_config->getValue('erp_etoday_settings/erp_authorization/password');
        $this->_host_name = $this->_config->getValue('erp_etoday_settings/erp_authorization/host_name');
        $this->_compcode = $this->_config->getValue('erp_etoday_settings/erp_authorization/compcode');
        $this->_enabled_test_mode = (bool) $this->_config->getValue('erp_etoday_settings/erp_authorization/enabled_test_mode');
        $this->_warehouse_code = $this->_config->getValue('erp_etoday_settings/erp_authorization/warehouse_code');
        $this->_color_code = $this->_config->getValue('erp_etoday_settings/color_settings/color_code');
        $this->_bags_codes = $this->_config->getValue('erp_etoday_settings/category_type_bags/bags_codes');
        $this->_shoe_codes = $this->_config->getValue('erp_etoday_settings/category_type_shoe/shoe_codes');
        $this->_categories_codes = $this->_config->getValue('erp_etoday_settings/categories/categories_codes');

        $this->setAllCategories();

        $this->request = new Client([
            'timeout' => 30,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);

        parent::configure();
    }

    /**
     * @return array
     */
    public function getAllCategories()
    {
        if (empty($this->all_categories)) {
            $this->setAllCategories();
        }

        return $this->all_categories;
    }

    public function setAllCategories()
    {
        $category_table = $this->categoryResource->getTable('catalog_category_entity_varchar');

        $select = $this->categoryResource
            ->getConnection()
            ->select()
            ->from($category_table)
            ->where('value IS NOT NULL')
            ->where('store_id = ?', \Magento\Store\Model\Store::DEFAULT_STORE_ID)
        ;

        $this->all_categories = $this->categoryResource->getConnection()->fetchAll($select);
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Hello World!');

        $last_point = 'GetProductList';

        $values = [
            'userAccount' => $this->getLogin(),
            'userPassword' => $this->getPassword(),
            'compCode' => $this->getCompcode(),
            'warehouseCode' => $this->getWarehouseCode(),
            'Branchpricedisplay' => 1,
            'CategoryDisplaySubCat' => 1,
            'Page' => 1,
        ];

        if ($this->isEnabledTestMode()) {
            $values['testmode'] = 1;
        }

        try {
            do {
                /** @var Response $response */
                $response = $this->request->get("{$this->getHostName()}/{$last_point}", [
                    'query' => $values,
                ]);
            } while ($this->parseBody($response) && $values['Page']++);
        } catch (\Throwable $e) {
            dump($e->getMessage());
        }

        if (!empty($this->products_data)) {
            $this->products_data = array_merge(...$this->products_data);

            /** @var \Generator|ErpProduct[] $data */
            $data = \iter\map(static function ($value) {
                return new ErpProduct($value);
            }, $this->products_data);

            foreach ($data as $datum) {

                if ($datum->isValid()){

                    $productId = $this->productResource->getIdBySku($datum->getIcProductCode());

                    /** @var \Magento\Catalog\Model\Product $product */
                    $product = $this->productFactory->create();

                    if ($productId){
                        $product->load($productId);
                        $product->setPrice($datum->getSalesPrice());

                        $product->addData([
                            'quantity_and_stock_status' => [
                                'is_in_stock' => $datum->getStockStatus(),
                                'qty'         => $datum->getUnrestrictStock()
                            ]
                        ]);

                        try {
                            $this->productResource->save($product);
                        } catch (\Exception $e) {
                            dump($e->getMessage());
                        }

                    }
                }
            }
        }

        dump(self::testMemory());
    }

    /**
     * @return string
     */
    public function getLogin(): string
    {
        return $this->_login;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->_password;
    }

    /**
     * @return string
     */
    public function getCompcode(): string
    {
        return $this->_compcode;
    }

    /**
     * @return string
     */
    public function getWarehouseCode(): string
    {
        return $this->_warehouse_code;
    }

    /**
     * @return bool
     */
    public function isEnabledTestMode(): bool
    {
        return $this->_enabled_test_mode;
    }

    /**
     * @param int $precision
     *
     * @return string
     */
    protected static function testMemory($precision = 2)
    {
        $bytes = memory_get_peak_usage();
        $units = ['b', 'kb', 'mb', 'gb', 'tb'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, \count($units) - 1);

        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision).' '.$units[$pow];
    }

    /**
     * @param string $bar_code
     *
     * @return string
     */
    public function getCategoryId(string $bar_code)
    {
        $category = '';
        $subCategory = '';

        $erpCategoryCode = substr($bar_code, 0, 2);
        $typeProduct = substr($bar_code, 1, 1);
        $erpSubCategoryCode = substr($bar_code, 2, 1);

        if (null !== $erpCategoryCode) {
            $cat = \iter\filter(static function ($item) use ($erpCategoryCode) {
                return $item['erp_category_code'] === $erpCategoryCode;
            }, $this->getCategoriesCodes());

            if ($cat->valid()) {
                $category = $cat->current()['category_path'];
            }
        }

        if (null !== $typeProduct) {
            if ('S' === $typeProduct) {
                $cat = \iter\filter(static function ($item) use ($erpSubCategoryCode) {
                    return $item['erp_shoe_code'] === $erpSubCategoryCode;
                }, $this->getShoeCodes());

                if ($cat->valid()) {
                    $subCategory = $cat->current()['shoe_category_value'];
                }
            } elseif ('B' === $typeProduct) {
                $cat = \iter\filter(static function ($item) use ($erpSubCategoryCode) {
                    return $item['erp_bags_code'] === $erpSubCategoryCode;
                }, $this->getBagsCodes());

                if ($cat->valid()) {
                    $subCategory = $cat->current()['bags_category_value'];
                }
            }
        }

        if (!empty($category)) {
            if (!empty($subCategory)) {
                $category .= "/{$subCategory}";
            }

            $entity_id = \iter\filter(static function ($item) use ($category) {
                if ($item['value'] === $category) {
                    return true;
                }

                $arrCategory = explode('/', $category);
                array_pop($arrCategory);

                $category = implode('/', $arrCategory);

                if ($item['value'] === $category) {
                    return true;
                }

                if ('default-category' === $item['value']) {
                    return true;
                }

                return false;
            }, $this->getAllCategories());

            if ($entity_id->valid()){
                return $entity_id->current()['entity_id'];
            }
        }

        return false;
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return bool
     */
    protected function parseBody(ResponseInterface $response)
    {
        if (200 === $response->getStatusCode()) {
            $body = $response->getBody()->getContents();

            $products_data = \GuzzleHttp\json_decode($body);

            if (!empty($products_data)) {
                $this->products_data[] = $products_data;

                return true;
            }

            return false;
        }

        return false;
    }
}
