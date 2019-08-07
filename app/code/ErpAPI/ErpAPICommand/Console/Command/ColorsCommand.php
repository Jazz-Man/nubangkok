<?php

namespace ErpAPI\ErpAPICommand\Console\Command;

use Encomage\ErpIntegration\Helper\CacheFile;
use Encomage\ErpIntegration\Helper\Data;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ColorsCommand.
 */
class ColorsCommand extends Command
{



    /**
     * @var \Magento\Framework\App\State
     */
    private $state;


    /**
     * @var \Encomage\ErpIntegration\Helper\CacheFile
     */
    private $cacheFile;
    /**
     * @var \Encomage\ErpIntegration\Helper\Data
     */
    private $helper;
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    private $productResource;

    /**
     * ColorsCommand constructor.
     *
     * @param \Magento\Framework\App\State                 $state
     * @param \Magento\Framework\ObjectManagerInterface    $objectManager
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product $productResource
     * @param \Encomage\ErpIntegration\Helper\Data         $helper
     *
     */
    public function __construct(
        State $state,
        ObjectManagerInterface $objectManager,
        ProductResource $productResource,
        Data $helper
    ) {
        $this->state                     = $state;

        parent::__construct();

        $this->cacheFile = new CacheFile($objectManager);
        $this->helper = $helper;
        $this->productResource = $productResource;
    }

    protected function configure()
    {
        $this->setName('erpapi:colors');

        try {
            $this->state->getAreaCode();
        } catch (LocalizedException $e) {
            $this->state->setAreaCode('adminhtml');
        }


        parent::configure();
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Setup colors!');


        $CacheFile = $this->cacheFile->getCacheFile();

        $data = $this->helper->getErpProductsObjects($CacheFile);

        $skus = [];

        foreach ($data as $datum){
            $skus[] = $datum->getBarCode();
        }

        $connection = $this->productResource->getConnection();

        $product_entity_table = $this->productResource->getTable('catalog_product_entity');

        $select = $connection->select()
                   ->from($product_entity_table,['sku', 'entity_id'])
                   ->where('sku NOT IN (?)', $skus);

        $result = $connection->fetchAll($select);


        dump($result);

    }

}
