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

namespace Goeasyship\Shipping\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Integration\Model\AuthorizationService;
use Magento\Integration\Model\IntegrationFactory;
use Magento\Integration\Model\Oauth\Token;
use Magento\Integration\Model\OauthService;

/**
 * Class InstallSchema
 *
 * @package Goeasyship\Shipping\Setup
 */
class InstallSchema implements InstallSchemaInterface
{

    /**
     * @var \Magento\Integration\Model\IntegrationFactory
     */
    protected $_integrationFactory;
    /**
     * @var \Magento\Integration\Model\OauthService
     */
    protected $_oauthService;
    /**
     * @var \Magento\Integration\Model\AuthorizationService
     */
    protected $_authorizationService;
    /**
     * @var \Magento\Integration\Model\Oauth\Token
     */
    protected $_token;

    /**
     * InstallSchema constructor.
     *
     * @param \Magento\Integration\Model\IntegrationFactory   $integrationFactory
     * @param \Magento\Integration\Model\OauthService         $oauthService
     * @param \Magento\Integration\Model\AuthorizationService $authorizationService
     * @param \Magento\Integration\Model\Oauth\Token          $token
     */
    public function __construct(
        IntegrationFactory $integrationFactory,
        OauthService $oauthService,
        AuthorizationService $authorizationService,
        Token $token
    ) {
        $this->_integrationFactory = $integrationFactory;
        $this->_oauthService = $oauthService;
        $this->_authorizationService = $authorizationService;
        $this->_token = $token;
    }

    /**
     * @param \Magento\Framework\Setup\SchemaSetupInterface   $setup
     * @param \Magento\Framework\Setup\ModuleContextInterface $context
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        $table = $installer->getTable('sales_shipment_track');
        $installer->getConnection()->addColumn(
            $table,
            'tracking_page_url',
            [
                'type' => Table::TYPE_TEXT,
                'size' => 255,
                'nullable' => false,
                'comment' => 'Tracking page url'
            ]
        );

        $this->createIntegration();

        $installer->endSetup();
    }

    protected function createIntegration()
    {
        $name = 'easyship';

        $integrationExists = $this->_integrationFactory->create()->load($name, 'name')->getData();
        if (empty($integrationExists)) {
            $integrationData = [
                'name' => $name,
                'status' => '1',
                'setup_type' => '0'
            ];

            // Code to create Integration
            $integrationFactory = $this->_integrationFactory->create();
            $integration = $integrationFactory->setData($integrationData);
            $integration->save();
            $integrationId = $integration->getId();
            $consumerName = 'Integration' . $integrationId;

            // Code to create consumer
            $consumer = $this->_oauthService->createConsumer(['name' => $consumerName]);
            $consumerId = $consumer->getId();
            $integration->setConsumerId($consumer->getId());
            $integration->save();

            // Code to grant permission
            $this->_authorizationService->grantAllPermissions($integrationId);

            // Code to Activate and Authorize
            $this->_token->createVerifierToken($consumerId);
            $this->_token->setType('access');
            $this->_token->save();
        }
    }
}
