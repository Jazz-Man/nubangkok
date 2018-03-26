<?php
namespace Encomage\Stories\Controller\Adminhtml\Grid;

use Magento\Backend\App\Action;
use Encomage\Stories\Api\StoriesRepositoryInterface;
use Encomage\Stories\Helper\Image;

/**
 * Class Delete
 * @package Encomage\Stories\Controller\Adminhtml\Grid
 */
class Delete extends Action
{
    /**
     * @var StoriesRepositoryInterface
     */
    private $storiesRepository;
    
    private $imageHelper;

    /**
     * Delete constructor.
     * @param Action\Context $context
     * @param Image $imageHelper
     * @param StoriesRepositoryInterface $storiesRepository
     */
    public function __construct(
        Action\Context $context,
        Image $imageHelper,
        StoriesRepositoryInterface $storiesRepository
    )
    {
        $this->imageHelper = $imageHelper;
        $this->storiesRepository = $storiesRepository;
        parent::__construct($context);
    }

    /**
     * @return $this
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('entity_id');
        if ($id) {
            try {
                $story = $this->storiesRepository->getById($id);
                $this->storiesRepository->deleteById($id);
                $this->imageHelper->deleteStoryImage($story->getImagePath());
                $this->messageManager->addSuccessMessage(__('Story has been deleted.'));
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Story was not deleted. Id: %1', $id));
            }
        }
        return $resultRedirect->setPath('stories/index/index');
    }
}