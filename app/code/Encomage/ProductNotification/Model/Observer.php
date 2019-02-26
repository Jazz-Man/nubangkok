<?php

namespace Encomage\ProductNotification\Model;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Encomage\ProductNotification\Model\ResourceModel\ProductNotification\CollectionFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableTypeInstance;
use Encomage\ProductNotification\Model\ResourceModel\ProductNotification as ResourceModel;
use Encomage\ProductNotification\Helper\Email;
use Magento\Framework\Url;

/**
 * Class Observer
 * @package Encomage\ProductNotification\Model
 */
class Observer implements ObserverInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var ConfigurableTypeInstance
     */
    protected $configurableTypeInstance;

    /**
     * @var ResourceModel
     */
    protected $resourceModel;

    /**
     * @var Email
     */
    protected $emailHelper;

    /**
     * @var UrlInterface
     */
    protected $url;

    /**
     * Observer constructor.
     * @param ProductRepositoryInterface $productRepository
     * @param CollectionFactory $collectionFactory
     * @param ConfigurableTypeInstance $configurableTypeInstance
     * @param ResourceModel $resourceModel
     * @param Email $emailHelper
     * @param Url $url
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        CollectionFactory $collectionFactory,
        ConfigurableTypeInstance $configurableTypeInstance,
        ResourceModel $resourceModel,
        Email $emailHelper,
        Url $url
    )
    {
        $this->productRepository = $productRepository;
        $this->collectionFactory = $collectionFactory;
        $this->configurableTypeInstance = $configurableTypeInstance;
        $this->resourceModel = $resourceModel;
        $this->emailHelper = $emailHelper;
        $this->url = $url;
    }

    /**
     * @param EventObserver $observer
     * @return $this|void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(EventObserver $observer)
    {
        $stockItem = $observer->getItem();
        /** @var \Magento\Catalog\Product $product*/
        $product = $this->productRepository->getById($stockItem->getProductId());
        $isInStock = $stockItem->getIsInStock();
        $qty = $stockItem->getQty();

        if ($isInStock && $qty > 0) {
            $productId = $product->getId();

            $parentProductId = $this->configurableTypeInstance->getParentIdsByChild($productId);
            if (!empty($parentProductId)) {
                $productId = array_shift($parentProductId);
            }
            /** @var \Encomage\ProductNotification\Model\ResourceModel\ProductNotification\Collection $collection */
            $collection = $this->collectionFactory->create();
            $collection->addFieldToFilter('product_id', ['eq' => $productId]);
            $idsForDelete = [];
            foreach ($collection as $item) {
               $productLink = $this->url->getUrl('catalog/product/view', ['id' => $product->getId(),'s'=>$product->getUrlKey(), '_nosid' => true, ]);

               /** @var \Encomage\ProductNotification\Model\ProductNotification $item */
                $idsForDelete[] = $item->getId();
                $this->emailHelper->sendEmail(
                    ['email'=>$item->getEmail()],
                    'general',
                    ['product_name'=>$item->getProductName(),'product_link'=>$productLink]);
            }

            $this->resourceModel->deleteRecordsByIds($idsForDelete);
        }

        return $this;
    }
}