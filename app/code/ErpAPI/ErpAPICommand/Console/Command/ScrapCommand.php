<?php

namespace ErpAPI\ErpAPICommand\Console\Command;

use Encomage\ErpIntegration\Helper\CacheFile;
use Encomage\ErpIntegration\Helper\ErpApiClient;
use Encomage\ErpIntegration\Helper\ErpApiScrap;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class HelloWorldCommand.
 */
class ScrapCommand extends Command
{

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Encomage\ErpIntegration\Helper\CacheFile
     */
    private $cacheFile;


    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var \Encomage\ErpIntegration\Helper\ErpApiClient
     */
    private $erpApiClient;
    /**
     * @var \Encomage\ErpIntegration\Helper\ErpApiScrap
     */
    private $erpApiScrap;

    /**
     * ScrapCommand constructor.
     *
     * @param ObjectManagerInterface                       $objectManager
     * @param \Magento\Store\Model\StoreManagerInterface   $storeManager
     * @param \Encomage\ErpIntegration\Helper\ErpApiClient $erpApiClient
     * @param \Encomage\ErpIntegration\Helper\CacheFile    $cacheFile
     * @param \Encomage\ErpIntegration\Helper\ErpApiScrap  $erpApiScrap
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        ErpApiClient $erpApiClient,
        CacheFile $cacheFile,
        ErpApiScrap $erpApiScrap
    ) {
        $this->objectManager = $objectManager;

        parent::__construct();

        $this->storeManager = $storeManager;


        $this->erpApiClient = $erpApiClient;
        $this->cacheFile    = $cacheFile;
        $this->erpApiScrap  = $erpApiScrap;
    }

    protected function configure()
    {
        $this->setName('erpapi:scrap')->setDescription('So much hello world.');

        $state = $this->objectManager->get(State::class);

        try {
            $state->getAreaCode();
        } catch (LocalizedException $e) {
            $state->setAreaCode('adminhtml');
        }

        parent::configure();
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface                 $input
     * @param \Symfony\Component\Console\Output\ConsoleOutput|OutputInterface $output
     *
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $this->erpApiScrap->setCliOutput($output);


        $this->storeManager->setCurrentStore(Store::DEFAULT_STORE_ID);

        $CacheFile = $this->cacheFile->getCacheFile();


        if ($CacheFile) {
            $this->erpApiScrap->dataProccesing($CacheFile);
        } else {

            do {

                $response = $this->erpApiClient->getData($this->erpApiScrap->getProductListPoint,
                    $this->erpApiScrap->getProductListQuery);

            } while ($this->erpApiScrap->parseBody($response) && $this->erpApiScrap->getProductListQuery['Page']++);

            if ( ! empty($this->erpApiScrap->getProductsData())) {
                $products_data = array_merge(...$this->erpApiScrap->getProductsData());

                $this->cacheFile->saveCacheFile($products_data);

                $this->erpApiScrap->dataProccesing($products_data);
            }
        }
    }

}
