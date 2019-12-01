<?php


namespace Encomage\ProductNotification\Plugin\Magento\CatalogInventory\Model\Stock;


use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Model\Stock\Item as ItemAlias;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Encomage\ProductNotification\Model\ResourceModel\ProductNotification as ResourceModel;
use Encomage\ProductNotification\Helper\Email as EmailAlias;
use Encomage\ProductNotification\Model\ResourceModel\ProductNotification\CollectionFactory as CollectionFactoryAlias;
use Magento\Framework\Url as UrlAlias;

/**
 * Class Item
 *
 * @package Encomage\ProductNotification\Plugin\Magento\CatalogInventory\Model\Stock
 */
class Item
{

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;
    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    private $configurableTypeInstance;
    /**
     * @var \Encomage\ProductNotification\Model\ResourceModel\ProductNotification
     */
    private $resourceModel;
    /**
     * @var \Encomage\ProductNotification\Helper\Email
     */
    private $emailHelper;
    /**
     * @var \Magento\Framework\Url
     */
    private $url;
    /**
     * @var \Encomage\ProductNotification\Model\ResourceModel\ProductNotification\CollectionFactory
     */
    private $collectionFactory;

    /**
     * Item constructor.
     *
     * @param \Magento\Catalog\Api\ProductRepositoryInterface                                         $productRepository
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable                            $configurableTypeInstance
     * @param \Encomage\ProductNotification\Model\ResourceModel\ProductNotification                   $resourceModel
     * @param \Encomage\ProductNotification\Helper\Email                                              $emailHelper
     * @param \Encomage\ProductNotification\Model\ResourceModel\ProductNotification\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Url                                                                  $url
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        Configurable $configurableTypeInstance,
        ResourceModel $resourceModel,
        EmailAlias $emailHelper,
        CollectionFactoryAlias $collectionFactory,
        UrlAlias $url
    )
    {
        $this->productRepository = $productRepository;
        $this->configurableTypeInstance = $configurableTypeInstance;
        $this->resourceModel = $resourceModel;
        $this->emailHelper = $emailHelper;
        $this->url = $url;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @param ItemAlias                                  $stockItem
     * @param                                            $result
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterSave(ItemAlias $stockItem, $result) {


        /** @var \Magento\Catalog\Model\Product $product*/
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



        return $result;
    }
}