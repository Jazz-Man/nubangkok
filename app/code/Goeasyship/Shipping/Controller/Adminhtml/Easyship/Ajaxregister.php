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

namespace Goeasyship\Shipping\Controller\Adminhtml\Easyship;

use Exception;
use Goeasyship\Shipping\Model\Api\Request;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\UrlInterface;
use Magento\Integration\Model\ResourceModel\Oauth\Token\Collection;
use Magento\Store\Model\Information as StoreInformation;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Ajaxregister
 *
 * @package Goeasyship\Shipping\Controller\Adminhtml\Easyship
 */
class Ajaxregister extends Action
{

    /**
     * @var \Magento\Integration\Model\ResourceModel\Integration\Collection
     */
    protected $_integration;
    /**
     * @var \Magento\Integration\Model\ResourceModel\Oauth\Consumer\Collection
     */
    protected $_consumer;
    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_authSession;
    /**
     * @var \Magento\Store\Model\Information
     */
    protected $_storeInfo;
    /**
     * @var \Magento\Store\Model\Store
     */
    protected $_storeManager;
    /**
     * @var \Magento\Integration\Model\ResourceModel\Oauth\Token\Collection
     */
    protected $_token;
    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $_productMetadata;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManagerInterface;
    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    protected $_config;
    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $_cacheTypeList;
    /**
     * @var \Goeasyship\Shipping\Model\Api\Request
     */
    protected $_easyshipApi;

    /**
     * Ajaxregister constructor.
     *
     * @param \Magento\Backend\App\Action\Context                                $context
     * @param \Magento\Integration\Model\ResourceModel\Integration\Collection    $integration
     * @param \Magento\Integration\Model\ResourceModel\Oauth\Consumer\Collection $consumer
     * @param \Magento\Integration\Model\ResourceModel\Oauth\Token\Collection    $token
     * @param \Magento\Backend\Model\Auth\Session                                $authSession
     * @param \Magento\Store\Model\Information                                   $storeInfo
     * @param \Magento\Store\Model\Store                                         $storeManager
     * @param \Magento\Store\Model\StoreManagerInterface                         $storeManagerInterface
     * @param \Magento\Config\Model\ResourceModel\Config                         $config
     * @param \Magento\Framework\App\Cache\TypeListInterface                     $cacheTypeList
     * @param \Magento\Framework\App\ProductMetadataInterface                    $productMetadata
     * @param \Goeasyship\Shipping\Model\Api\Request                             $easyshipApi
     */
    public function __construct(
        Context $context,
        \Magento\Integration\Model\ResourceModel\Integration\Collection $integration,
        \Magento\Integration\Model\ResourceModel\Oauth\Consumer\Collection $consumer,
        Collection $token,
        Session $authSession,
        StoreInformation $storeInfo,
        Store $storeManager,
        StoreManagerInterface $storeManagerInterface,
        Config $config,
        TypeListInterface $cacheTypeList,
        ProductMetadataInterface $productMetadata,
        Request $easyshipApi
    ) {
        parent::__construct($context);

        $this->_integration = $integration;
        $this->_consumer = $consumer;
        $this->_authSession = $authSession;
        $this->_storeInfo = $storeInfo;
        $this->_storeManager = $storeManager;
        $this->_token = $token;
        $this->_productMetadata = $productMetadata;
        $this->_storeManagerInterface = $storeManagerInterface;
        $this->_config = $config;
        $this->_cacheTypeList = $cacheTypeList;
        $this->_easyshipApi = $easyshipApi;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $response = [];
        try {
            $params = $this->getRequest()->getParams();
            if (!empty($params)) {
                $request = [];
                $storeId = filter_var($this->getRequest()->getParam('store_id'), FILTER_SANITIZE_SPECIAL_CHARS);

                // get easyship oauth consumer key and secret
                $request['oauth'] = $this->_getOAuthInfo();
                $request['user'] = $this->_getUserInfo();
                $request['company'] = $this->_getCompanyInfo();
                $request['store'] = $this->_getStoreInfo($storeId);

                $response = $this->_easyshipApi->registrationsRequest($request);
                $this->getResponse()->setBody(json_encode($response));
            } else {
                throw new Exception('Method not supported');
            }
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
            $this->getResponse()->setBody(json_encode($response));
        }

        $this->getResponse()
            ->clearHeaders()
            ->setHeader('Status', 200)
            ->setHeader('Content-type', 'application/json', true);
    }

    /**
     * Return easyship integrations keys and tokens
     * @return array|bool
     * @throws \Exception
     */
    protected function _getOAuthInfo()
    {
        $response = [];
        $integration = $this->_integration
            ->addFieldToFilter('name', 'easyship')
            ->setPageSize(1)
            ->setCurPage(1)
            ->getLastItem();

        if ($integration === null) {
            throw new Exception('Something was wrong please create easyship integration and activated it');
        }

        $consumerId = $integration->getConsumerId();
        if (empty($consumerId)) {
            return false;
        }

        $token = $this->_token
            ->addFieldToFilter('consumer_id', $consumerId)
            ->setPageSize(1)
            ->setCurPage(1)
            ->getLastItem();

        if (!$token->getId()) {
            return false;
        }

        $consumer = $this->_consumer->getItemById($consumerId);
        if (!$consumer->getId()) {
            return false;
        }

        $response['token'] = $token->getToken();
        $response['token_secret'] = $token->getSecret();
        $response['consumer_key'] = $consumer->getKey();
        $response['consumer_secret'] = $consumer->getSecret();

        return $response;
    }

    /**
     * Return user information
     * @return array
     * @throws \Exception
     */
    protected function _getUserInfo()
    {
        $response = [];
        $user = $this->_authSession->getUser();
        if (!$user->getId()) {
            throw new Exception('User session is not found');
        }

        $response['email'] = $user->getEmail();

        $response['first_name'] = $user->getFirstname();
        $response['last_name'] = $user->getLastname();
        $response['mobile_phone'] = $this->_storeManager->getConfig(StoreInformation::XML_PATH_STORE_INFO_PHONE);

        return $response;
    }

    /**
     * Return company information
     * @return array
     * @throws \Exception
     */
    protected function _getCompanyInfo()
    {
        $response = [];

        $response['name'] = $store = $this->_storeManager->getConfig(StoreInformation::XML_PATH_STORE_INFO_NAME);
        $response['country_code'] = $this->_storeManager->getConfig(StoreInformation::XML_PATH_STORE_INFO_COUNTRY_CODE);

        if (empty($response['name']) || empty($response['country_code'])) {
            throw new Exception('Please, fill store name and store country code (System -> General -> Store Information)');
        }

        return $response;
    }

    /**
     * Return store information
     * @param $storeId
     * @return array
     * @throws \Exception
     */
    protected function _getStoreInfo($storeId)
    {
        if (!$storeId) {
            throw new Exception('store not found');
        }

        $response = [];
        $response['id'] = $storeId;
        $response['name'] = $this->_storeManager->getConfig(StoreInformation::XML_PATH_STORE_INFO_NAME);
        $response['url'] = $this->_storeManagerInterface->getStore()->getBaseUrl(UrlInterface::URL_TYPE_WEB);
        $response['version'] = $this->_productMetadata->getVersion();

        return $response;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Goeasyship_Shipping::easyship');
    }
}
