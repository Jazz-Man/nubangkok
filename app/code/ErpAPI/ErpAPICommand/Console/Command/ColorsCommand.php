<?php

namespace ErpAPI\ErpAPICommand\Console\Command;

use Encomage\ErpIntegration\Helper\CacheFile;
use Encomage\ErpIntegration\Helper\Data;
use Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollectionAlias;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
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
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * ColorsCommand constructor.
     *
     * @param \Magento\Framework\App\State                    $state
     * @param \Magento\Framework\ObjectManagerInterface       $objectManager
     *
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Model\ResourceModel\Product    $productResource
     * @param \Encomage\ErpIntegration\Helper\Data            $helper
     */
    public function __construct(
        State $state,
        ObjectManagerInterface $objectManager,
        ProductRepositoryInterface $productRepository,
        ProductResource $productResource,
        Data $helper
    ) {
        $this->state                     = $state;

        parent::__construct();

        $this->cacheFile = new CacheFile($objectManager);
        $this->helper = $helper;
        $this->productResource = $productResource;
        $this->objectManager = $objectManager;
        $this->productRepository = $productRepository;
    }

    protected function configure()
    {
        $this->setName('erpapi:colors');

        try {
            $this->state->setAreaCode('adminhtml');
        } catch (LocalizedException $e) {
//            $this->state->setAreaCode('adminhtml');
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
        $this->objectManager->get(Registry::class)->register('isSecureArea', true);

        $output->writeln('Setup colors!');

        /** @var ProductCollectionAlias $collection */

        $collection = $this->objectManager->create(CollectionFactory::class)->create();
        $collection->addAttributeToSelect('*');
        $collection->getProductTypeIds();
        $collection->load();


        /** @var \Magento\Catalog\Model\Product $product */
        foreach ($collection as $product){

            try {

                if ($product->getTypeId() === 'simple'){
                    dump("Delete Product: '{$product->getName()}'");
                    $product->delete();
                }
            } catch (Exception $e) {
                dump($e->getMessage());
            }
        }

//        dump($this->registry);

    }

}
