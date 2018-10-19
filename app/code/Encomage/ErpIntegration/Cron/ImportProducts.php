<?php
namespace Encomage\ErpIntegration\Cron;

use Psr\Log\LoggerInterface;
use Encomage\ErpIntegration\Model\Api\Product;
use Encomage\ErpIntegration\Helper\Data;

class ImportProducts
{
    /**
     * @var Product
     */
    private $apiProduct;
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var Data
     */
    protected $_helper;

    /**
     * ImportProducts constructor.
     *
     * @param LoggerInterface $logger
     * @param Product $apiProduct
     * @param Data $data
     */
    public function __construct(LoggerInterface $logger, Product $apiProduct, Data $data)
    {
        $this->logger = $logger;
        $this->apiProduct = $apiProduct;
        $this->_helper = $data;
    }

    /**
     * Write to system.log
     *
     * @return void
     */
    public function execute()
    {
        $this->logger->info('Start Cron Import');
        set_time_limit($this->_helper->getTimeLimit());
        try {
            $this->apiProduct->importAllProducts();
            $this->logger->info('Products was imported');
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        ini_restore('max_execution_time');
        $this->logger->info('Finish Cron Import');
    }
}