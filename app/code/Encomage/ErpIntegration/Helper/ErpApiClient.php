<?php

namespace Encomage\ErpIntegration\Helper;

use Encomage\ErpIntegration\Logger\Logger;
use Exception;
use GuzzleHttp\Client;
use function GuzzleHttp\json_decode as json_decodeAlias;
use GuzzleHttp\Middleware;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Request;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Psr\Http\Message\ResponseInterface;

/**
 * Class ErpApiClient.
 */
class ErpApiClient extends AbstractHelper
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
     * @var array
     */
    private $defaults;
    /**
     * @var string
     */
    private $warehouseCode;
    /**
     * @var \Encomage\ErpIntegration\Logger\Logger
     */
    private $logger;

    /**
     * ErpApiClient constructor.
     *
     * @param \Magento\Framework\App\Helper\Context  $context
     * @param \Encomage\ErpIntegration\Logger\Logger $logger
     */
    public function __construct(
        Context $context,
        Logger $logger
    )
    {
        parent::__construct($context);

        $this->logger = $logger;

        $host_name = $this->scopeConfig->getValue(self::HOST_NAME);

        $this->client = new Client([
            'timeout' => 2000,
            'base_uri' => "{$host_name}/",
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);

        $this->warehouseCode = $this->scopeConfig->getValue(self::WAREHOUSE_CODE);


        $this->defaults = [
            'userAccount' => $this->scopeConfig->getValue(self::LOGIN),
            'userPassword' => $this->scopeConfig->getValue(self::PASSWORD),
            'compCode' => $this->scopeConfig->getValue(self::COMPCODE),
            'warehouseCode' => $this->warehouseCode,
        ];

        if ((bool) $this->scopeConfig->getValue(self::TEST_MODE)) {
            $this->defaults['testmode'] = 1;
        }
    }

    /**
     * @return string
     */
    public function getWarehouseCode(): string
    {
        return $this->warehouseCode;
    }

    /**
     * @param string $point
     * @param array  $query
     *
     * @param array  $options
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getData(string $point,array $query = [], array $options = []): ResponseInterface
    {
        if (!empty($this->defaults['testmode'])){
            unset($this->defaults['testmode']);
        }


        $query = array_merge($this->defaults, $query);

        $_options = [
            'query' => $query,
            'handler' => $this->requestHandler(),
        ];

        $_options = array_merge($_options, $options);

        return $this->client->get($point,$_options);
    }

    /**
     * @param string $point
     * @param array  $query
     * @param array  $options
     *
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function postJsonData(string $point, array $query = [], array $options = []): PromiseInterface
    {
        $_options = [
            'query' => $this->defaults,
            'json' => $query,
            'handler' => $this->requestHandler(),
        ];

        $_options = array_merge($_options, $options);

        return $this->client->postAsync($point, $_options);
    }

    /**
     * @return \GuzzleHttp\Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return bool|\Exception|\stdClass
     */
    public function parseBody(ResponseInterface $response)
    {
        $result = false;

        if (200 === $response->getStatusCode()) {
            $body = $response->getBody()->getContents();

            try {
                $response_data = $this->jsonDecode($body);

                $result = !empty($response_data) ? $response_data : false;
            } catch (Exception $exception) {
                return $exception;
            }
        }

        return $result;
    }

    /**
     * @return mixed
     */
    public function requestHandler(){
        $clientHandler = $this->getClient()->getConfig('handler');

        $tapMiddleware = Middleware::tap([$this,'logRequest']);

        return $tapMiddleware($clientHandler);
    }

    /**
     * @param \GuzzleHttp\Psr7\Request $request
     * @param array                    $options
     */
    public function logRequest(Request $request,array $options = []): void
    {
        /** @var \GuzzleHttp\Psr7\Uri $uri */
        $uri = $request->getUri();

        $this->logger->info("'{$uri->getPath()}' Request Method:", [(string) $request->getMethod()]);
        $this->logger->info("'{$uri->getPath()}' Request Uri:", [(string) $uri]);

        if (!empty((string)$request->getBody())){
            $Body = $this->jsonDecode($request->getBody(), true);
            $this->logger->info("'{$uri->getPath()}' Request Body:", $Body);

        }
    }

    /**
     * @param      $data
     *
     * @param bool $assoc
     *
     * @return mixed
     */
    public function jsonDecode($data, $assoc = false)
    {
        return json_decodeAlias((string)$data, $assoc, 512, JSON_UNESCAPED_UNICODE);
    }
}
