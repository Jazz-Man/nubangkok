<?php

namespace Encomage\DropdownFields\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;

/**
 * Class Data
 * @package Encomage\DropdownFields\Helper
 */
class Data extends AbstractHelper
{

    const DOMAIN_REGION_NAME = 'dropdown_fields/general/domain_region';
    const DOMAIN_CITY_NAME = 'dropdown_fields/general/domain_city';
    const API_KEY = 'dropdown_fields/general/api_key';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Data constructor.
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    /**
     * @return mixed
     */
    public function getApiRegionDomainName()
    {
        return $this->scopeConfig->getValue(self::DOMAIN_REGION_NAME);
    }

    /**
     * @return mixed
     */
    public function getApiCityDomainName()
    {
        return $this->scopeConfig->getValue(self::DOMAIN_CITY_NAME);
    }

    /**
     * @return mixed
     */
    public function getApiKeyValue()
    {
        return $this->scopeConfig->getValue(self::API_KEY);
    }
}