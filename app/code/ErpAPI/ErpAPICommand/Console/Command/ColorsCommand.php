<?php

namespace ErpAPI\ErpAPICommand\Console\Command;

use Exception;
use Magento\Catalog\Model\ProductRepository;
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
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;
    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    private $productRepository;
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * ColorsCommand constructor.
     *
     * @param \Magento\Framework\App\State              $state
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Catalog\Model\ProductRepository  $productRepository
     * @param \Magento\Framework\Registry               $registry
     */
    public function __construct(
        State $state,
        ObjectManagerInterface $objectManager,
        ProductRepository $productRepository,
        Registry $registry
    ) {
        $this->state = $state;
        $this->objectManager = $objectManager;
        $this->productRepository = $productRepository;
        $this->registry = $registry;
        parent::__construct();
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

        $this->registry->register('isSecureArea', true);

//        $this->objectManager->get(Registry::class)->register('isSecureArea', true);

        $output->writeln('Setup colors!');

        /** @var ProductCollectionAlias $collection */
        $collection = $this->objectManager->create(CollectionFactory::class)->create();
        $collection->addAttributeToSelect('*');
        $collection->getProductTypeIds();
        $collection->load();

        /** @var \Magento\Catalog\Model\Product $product */
        foreach ($collection as $product) {


            try {
//                dump("Delete Product: '{$product->getName()}'");
//                $product->delete();
                $this->productRepository->delete($product);
            } catch (Exception $e) {
//                dump($e->getMessage());
            }
        }
    }
}
