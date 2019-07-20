<?php

namespace ErpAPI\ErpAPICommand\Helper;

use GuzzleHttp\Client;
use function GuzzleHttp\json_decode as json_decodeAlias;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class ApiClient.
 */
class ApiClient
{
    public const COMPCODE = 'erp_etoday_settings/erp_authorization/compcode';

    public const HOST_NAME = 'erp_etoday_settings/erp_authorization/host_name';

    public const LOGIN = 'erp_etoday_settings/erp_authorization/login';

    public const PASSWORD = 'erp_etoday_settings/erp_authorization/password';

    public const TEST_MODE = 'erp_etoday_settings/erp_authorization/enabled_test_mode';

    public const WAREHOUSE_CODE = 'erp_etoday_settings/erp_authorization/warehouse_code';

    /**
     * @var \GuzzleHttp\Client
     */
    private $client;
    /**
     * @var string
     */
    private $_host_name;

    /**
     * @var array
     */
    private $defaults;

    /**
     * ApiClient constructor.
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->_host_name = $scopeConfig->getValue(self::HOST_NAME);

        $this->client = new Client([
            'timeout' => 30,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);

        $this->defaults = [
            'userAccount' => $scopeConfig->getValue(self::LOGIN),
            'userPassword' => $scopeConfig->getValue(self::PASSWORD),
            'compCode' => $scopeConfig->getValue(self::COMPCODE),
            'warehouseCode' => $scopeConfig->getValue(self::WAREHOUSE_CODE),
        ];

        if ((bool) $scopeConfig->getValue(self::TEST_MODE)) {
            $this->defaults['testmode'] = 1;
        }
    }

    /**
     * @param string $point
     * @param array  $query
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getData(string $point, array $query = [])
    {
        $query = array_merge($this->defaults, $query);

        return $this->client->get("{$this->_host_name}/{$point}", [
            'query' => $query,
        ]);
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return bool|mixed
     */
    public function parseBody(ResponseInterface $response)
    {
        if (200 === $response->getStatusCode()) {
            $body = $response->getBody()->getContents();

            $response_data = json_decodeAlias($body);

            return !empty($response_data) ? $response_data : false;
        }

        return false;
    }
}
