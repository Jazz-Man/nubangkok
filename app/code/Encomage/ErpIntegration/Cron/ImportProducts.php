<?php
namespace Encomage\ErpIntegration\Cron;
use Psr\Log\LoggerInterface;
use Encomage\ErpIntegration\Model\Api\Product;

class ImportProducts
{
    private $apiProduct;
    
    protected $logger;

    public function __construct(LoggerInterface $logger, Product $apiProduct) {
        $this->logger = $logger;
        $this->apiProduct = $apiProduct;
    }

    /**
     * Write to system.log
     *
     * @return void
     */
    public function execute() {
        $this->logger->info('Start Cron');
        $this->apiProduct->importAllProducts();
        $this->logger->info('Finish Cron');
    }
}