<?php
/**
 * Easyship.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Easyship.com license that is
 * available through the world-wide-web at this URL:
 * https://www.apache.org/licenses/LICENSE-2.0
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Goeasyship
 * @package     Goeasyship_Shipping
 * @copyright   Copyright (c) 2018 Easyship (https://www.easyship.com/)
 * @license     https://www.apache.org/licenses/LICENSE-2.0
 */

namespace Goeasyship\Shipping\Model\Api;

use Goeasyship\Shipping\Model\Logger\Logger;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Zend_Http_Client;

/**
 * Class Request
 *
 * @package Goeasyship\Shipping\Model\Api
 */
class Request
{

    const BASE_ENDPOINT = 'https://api.easyship.com/';

    const BASE_SETTINGS_PATH = 'easyship_options/ec_shipping/';

    protected $_scopeConfig;

    protected $_config;

    protected $_storeManager;

    protected $_token;

    protected $logger;

    /**
     * Request constructor.
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Config\Model\ResourceModel\Config         $config
     * @param \Magento\Store\Model\StoreManagerInterface         $storeManager
     * @param \Goeasyship\Shipping\Model\Logger\Logger           $logger
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Config $config,
        StoreManagerInterface $storeManager,
        Logger $logger
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_config = $config;
        $this->_storeManager = $storeManager;
        $this->logger = $logger;
    }

    /**
     * Registration app
     *
     * @param $requestBody
     *
     * @return bool|mixed
     * @throws \Zend_Http_Client_Exception
     */
    public function registrationsRequest($requestBody)
    {
        $endpoint = self::BASE_ENDPOINT . 'api/v1/magento/registrations';

        return $this->_doRequest($endpoint, $requestBody, null, false);
    }

    /**
     * Return rates
     *
     * @param $requestBody
     *
     * @return bool|mixed
     * @throws \Zend_Http_Client_Exception
     */
    public function getQuotes($requestBody)
    {
        $endpoint = self::BASE_ENDPOINT . 'rate/v1/magento';

        return $this->_doRequest($endpoint, $requestBody->getData());
    }

    /**
     * @param string $endpoint
     * @param array  $requestBody
     * @param null   $headers
     * @param bool   $isAuth
     * @param string $method
     *
     * @return bool|mixed
     * @throws \Zend_Http_Client_Exception
     */
    protected function _doRequest($endpoint, array $requestBody, $headers = null, $isAuth = true, $method = 'POST')
    {
        $client = new Zend_Http_Client($endpoint);
        $client->setMethod($method);

        if ($isAuth) {
            $client->setHeaders('Authorization', 'Bearer ' . $this->getToken());
        }

        if ($headers === null) {
            $client->setHeaders([
                'Content-Type' => 'application/json'
            ]);
        } elseif (is_array($headers)) {
            $client->setHeaders($headers);
        }

        $client->setRawData(json_encode($requestBody));

        $response = $client->request($method);

        if ($response === null || !$response->isSuccessful()) {
            $this->loggerRequest($endpoint, $response->getStatus());
            return false;
        }
        $result = json_decode($response->getBody(), true);

        $this->loggerRequest($endpoint, $response->getStatus(), $result);

        return $result;
    }

    /**
     * Get Token
     * @return string
     */
    protected function getToken()
    {
        if (empty($this->_token)) {
            $this->_token = $this->_scopeConfig->getValue(
                self::BASE_SETTINGS_PATH . 'token',
                ScopeInterface::SCOPE_STORE
            );
        }

        return $this->_token;
    }

    /**
     * Add line to log file
     * @param $endpoint
     * @param $status
     * @param null $response
     */
    protected function loggerRequest($endpoint, $status, $response = null)
    {
        $this->logger->info($endpoint . ' : ' . $status);
        if (is_array($response)) {
            $this->logger->info(json_encode($response));
        } elseif (!empty($response)) {
            $this->logger->info($response);
        }
    }
}
