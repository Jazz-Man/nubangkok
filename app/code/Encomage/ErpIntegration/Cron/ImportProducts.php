<?php
namespace Encomage\ErpIntegration\Cron;

use Psr\Log\LoggerInterface;
use Encomage\ErpIntegration\Model\Api\Product;


/**
 * Class ImportProducts
 *
 * @package Encomage\ErpIntegration\Cron
 */
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
     *
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
        $page = 0;
        try {
            for ($i = 1; $page < $i; $i++) {
                $apiData = $this->apiProduct->getDataFromApi($page);
                if (empty($apiData)) {
                    $this->logger->info('Products was imported');
                    break;
                }
                $this->apiProduct->createProducts($apiData);
                $page++;
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        $this->logger->info('Finish Cron Import');
    }
}