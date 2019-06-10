<?php
/**
 * NuBangkok
 *
 * @category NuBangkok-module
 * @package  Encomage
 * @author   Encomage <hello@encomage.com>
 * @license  OSL https://opensource.org/licenses/OSL-3.0
 * @link     http://encomage.com
 */

namespace Encomage\ErpIntegration\Logger;

use Monolog\Logger;
use Magento\Framework\Logger\Handler\Base;

class Handler extends Base
{
    /**
     * Logging level
     *
     * @var int
     */
    protected $loggerType = Logger::INFO;

    /**
     * File name
     *
     * @var string
     */
    protected $fileName = '/var/log/erp_integration.log';
}