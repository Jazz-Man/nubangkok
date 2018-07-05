<?php
namespace Encomage\ErpIntegration\Cron;

use Psr\Log\LoggerInterface;
use Encomage\ErpIntegration\Model\Api\Product;

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
     * ImportProducts constructor.
     * @param LoggerInterface $logger
     * @param Product $apiProduct
     */
    public function __construct(LoggerInterface $logger, Product $apiProduct)
    {
        $this->logger = $logger;
        $this->apiProduct = $apiProduct;
    }

    /**
     * Write to system.log
     *
     * @return void
     */
    public function execute()
    {
        $this->logger->info('Start Cron Import');
        try {
            $this->apiProduct->importAllProducts();
            $this->logger->info('Products was imported');
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        $this->logger->info('Finish Cron Import');
    }
}