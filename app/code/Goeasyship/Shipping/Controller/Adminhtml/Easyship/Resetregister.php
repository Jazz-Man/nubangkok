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

use Braintree\Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\Cache\TypeListInterface;

/**
 * Class Resetregister
 *
 * @package Goeasyship\Shipping\Controller\Adminhtml\Easyship
 */
class Resetregister extends Action
{

    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    protected $_config;
    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $_cacheTypeList;

    /**
     * Resetregister constructor.
     *
     * @param \Magento\Backend\App\Action\Context            $context
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Config\Model\ResourceModel\Config     $config
     */
    public function __construct(
        Context $context,
        TypeListInterface $cacheTypeList,
        Config $config
    ) {
        parent::__construct($context);

        $this->_config = $config;
        $this->_cacheTypeList = $cacheTypeList;
    }

    /**
     * Execute action based on request and return result
     *
     * Note: Request will be added as operation argument in future
     *
     * @return bool|\Goeasyship\Shipping\Controller\Adminhtml\Easyship\Resetregister
     * @throws \Braintree\Exception
     */
    public function execute()
    {
        $storeId = filter_var($this->getRequest()->getParam('store_id'), FILTER_SANITIZE_SPECIAL_CHARS);
        if (!$storeId) {
            return false;
        }
        try {
            $this->_config->deleteConfig('easyship_options/ec_shipping/token', 'default', $storeId);
            $this->_cacheTypeList->cleanType('config');
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }

        return $this;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Goeasyship_Shipping::easyship');
    }
}
