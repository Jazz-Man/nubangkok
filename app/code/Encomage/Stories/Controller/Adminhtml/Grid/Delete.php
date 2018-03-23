<?php
namespace Encomage\Stories\Controller\Adminhtml\Grid;

use Magento\Backend\App\Action;
use Encomage\Stories\Api\StoriesRepositoryInterface;

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

    /**
     * Delete constructor.
     * @param Action\Context $context
     * @param StoriesRepositoryInterface $storiesRepository
     */
    public function __construct(
        Action\Context $context,
        StoriesRepositoryInterface $storiesRepository
    )
    {
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
                $this->storiesRepository->deleteById($id);
                $this->messageManager->addSuccessMessage(__('Story has been deleted.'));
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Story was not deleted. Id: %1', $id));
            }
        }
        return $resultRedirect->setPath('stories/index/index');
    }
}