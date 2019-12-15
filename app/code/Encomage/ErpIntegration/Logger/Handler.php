<?php


namespace Encomage\ErpIntegration\Logger;


use Magento\Framework\Logger\Handler\Base;

/**
 * Class Handler
 *
 * @package Encomage\ErpIntegration\Logger
 */
class Handler extends Base
{
    /**
     * Logging level
     * @var int
     */
    protected $loggerType = Logger::INFO;

    /**
     * File name
     * @var string
     */
    protected $fileName = '/var/log/erp.log';

}