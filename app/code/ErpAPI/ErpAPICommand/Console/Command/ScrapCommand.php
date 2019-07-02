<?php

namespace ErpAPI\ErpAPICommand\Console\Command;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;
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
     * @var Json
     */
    protected $_serializerJson;
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
     * ScrapCommand constructor.
     *
     * @param ScopeConfigInterface                         $scopeConfig
     * @param \Magento\Framework\Serialize\Serializer\Json $serializerJson
     */
    public function __construct(ScopeConfigInterface $scopeConfig, Json $serializerJson)
    {
        $this->_config = $scopeConfig;
        $this->_serializerJson = $serializerJson;
        parent::__construct();
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
    public function getHostName(): string
    {
        return $this->_host_name;
    }

    /**
     * @return string
     */
    public function getCategoriesCodes(): string
    {
        return $this->_categories_codes;
    }

    /**
     * @return string
     */
    public function getShoeCodes(): string
    {
        return $this->_shoe_codes;
    }

    /**
     * @return string
     */
    public function getBagsCodes(): string
    {
        return $this->_bags_codes;
    }

    /**
     * @return string
     */
    public function getColorCode(): string
    {
        return $this->_color_code;
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
     * @return string
     */
    public function getCompcode(): string
    {
        return $this->_compcode;
    }

    protected function configure()
    {
        $this->setName('erpapi:scrap')->setDescription('So much hello world.');

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
            dump(array_merge(...$this->products_data));
        }
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
