<?php

namespace Encomage\ErpIntegration\Cron;

use Encomage\ErpIntegration\Helper\ErpApiClient;
use Encomage\ErpIntegration\Helper\ErpApiScrap;
use Encomage\ErpIntegration\Logger\Logger;
use GuzzleHttp\Psr7\Response;


/**
 * Class ImportProducts
 *
 * @package Encomage\ErpIntegration\Cron
 */
class ImportProducts
{

    /**
     * @var array
     */
    private $products_data = [[]];

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    /**
     * @var \Encomage\ErpIntegration\Helper\ErpApiScrap
     */
    private $erpApiScrap;
    /**
     * @var \Encomage\ErpIntegration\Helper\ErpApiClient
     */
    private $erpApiClient;

    /**
     * ImportProducts constructor.
     *
     * @param \Encomage\ErpIntegration\Logger\Logger       $logger
     * @param \Encomage\ErpIntegration\Helper\ErpApiClient $erpApiClient
     * @param \Encomage\ErpIntegration\Helper\ErpApiScrap  $erpApiScrap
     */
    public function __construct(

        Logger $logger,
        ErpApiClient $erpApiClient,
        ErpApiScrap $erpApiScrap
    ) {


        $this->logger       = $logger;
        $this->erpApiClient = $erpApiClient;
        $this->erpApiScrap  = $erpApiScrap;
    }

    /**
     * Write to system.log
     *
     * @return void
     */
    public function execute(): void
    {
        $this->logger->info('Start ERP Cron Import');

        do {
            /** @var Response $response */
            $response = $this->erpApiClient->getData($this->erpApiScrap->getProductListPoint,
                $this->erpApiScrap->getProductListQuery);
        } while ($this->erpApiScrap->parseBody($response) && $this->erpApiScrap->getProductListQuery['Page']++);

        if ( ! empty($this->products_data)) {

            $this->erpApiScrap->dataProccesing();
        }

        $this->logger->info('Finish ERP Cron Import');

    }


}