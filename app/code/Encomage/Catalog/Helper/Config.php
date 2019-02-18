<?php

namespace Encomage\Catalog\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Class Data
 *
 * @package Encomage\Catalog\Helper
 */
class Config extends AbstractHelper
{
    const USE_SIMPLE_INSTEAD_CONFIGURABLE = 'catalog/frontend/use_simple_instead_configurable';

    /**
     * @return bool
     */
    public function isUseSimpleInsteadConfigurable()
    {
        return $this->scopeConfig->isSetFlag(self::USE_SIMPLE_INSTEAD_CONFIGURABLE);
    }
}