<?php
namespace Encomage\Stories\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Ui\Component\MassAction\Filter;
use Encomage\Stories\Model\ResourceModel\Stories\CollectionFactory;
use Encomage\Stories\Model\ResourceModel\Stories as StoriesResource;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Encomage\Stories\Helper\Image;

/**
 * Class MassDelete
 * @package Encomage\Stories\Controller\Adminhtml\Index
 */
class MassDelete extends Action
{
    /**
     * @var Filter
     */
    private $filter;
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;
    /**
     * @var StoriesResource
     */
    private $storiesResource;
    /**
     * @var Image
     */
    private $imageHelper;

    /**
     * MassDelete constructor.
     * @param Action\Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param Image $imageHelper
     * @param StoriesResource $storiesResource
     */
    public function __construct(
        Action\Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        Image $imageHelper,
        StoriesResource $storiesResource
    )
    {
        $this->imageHelper = $imageHelper;
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->storiesResource = $storiesResource;
        parent::__construct($context);
    }

    /**
     * @return $this
     * @throws LocalizedException
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $collectionSize = $collection->getSize();
        try {
            $itemsForDelete = $collection->getItems();
            $result = $this->storiesResource->massDeleteById($collection->getAllIds());
            if ($result && $result > 0) {
                foreach ($itemsForDelete as $item) {
                    if ($item->getImagePath()) {
                        $this->imageHelper->deleteStoryImage($item->getImagePath());
                    }
                }
            }

            $this->messageManager->addSuccessMessage(__('A total of %1 row(s) has been deleted.', $collectionSize));
        } catch (\Exception $e) {
            throw new LocalizedException(__($e));
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/index');
    }
}