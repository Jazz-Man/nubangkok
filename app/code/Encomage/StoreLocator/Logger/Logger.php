<?php
/**
 * @author Andrey Bondarenko
 * @link http://encomage.com
 * @mail info@encomage.com
 */

namespace Encomage\StoreLocator\Logger;

/**
 * Class Logger
 * @package Encomage\StoreLocator\Logger
 */
class Logger extends \Monolog\Logger
{
    /**
     * @param \Exception $e
     * @return $this
     */
    public function logException(\Exception $e)
    {
        $this->critical($e->getMessage() . ' ' . $e->getTraceAsString());
        return $this;
    }
}