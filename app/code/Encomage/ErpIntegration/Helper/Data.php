<?php
namespace Encomage\ErpIntegration\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    const XML_PATH_PHP_TIME_LIMIT = 'erp_etoday_settings/erp_authorization/time_limit';
    /**
     * @return mixed
     */
    public function getTimeLimit()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_PHP_TIME_LIMIT);
    }
}