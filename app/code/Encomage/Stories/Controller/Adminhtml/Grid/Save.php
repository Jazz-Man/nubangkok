<?php
namespace Encomage\Stories\Controller\Adminhtml\Grid;

use Magento\Backend\App\Action;
use Encomage\Stories\Api\StoriesRepositoryInterface;
use Encomage\Stories\Api\Data\StoriesInterfaceFactory;

/**
 * Class Save
 * @package Encomage\Stories\Controller\Adminhtml\Grid
 */
class Save extends Action
{
    /**
     * @var StoriesInterfaceFactory
     */
    private $storiesFactory;
    /**
     * @var StoriesRepositoryInterface
     */
    private $storiesRepository;

    /**
     * Save constructor.
     * @param Action\Context $context
     * @param StoriesRepositoryInterface $storiesRepository
     * @param StoriesInterfaceFactory $storiesFactory
     */
    public function __construct(
        Action\Context $context,
        StoriesRepositoryInterface $storiesRepository,
        StoriesInterfaceFactory $storiesFactory
    )
    {
        $this->storiesRepository = $storiesRepository;
        $this->storiesFactory = $storiesFactory;
        parent::__construct($context);
    }

    /**
     * @return $this
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $params = $this->getRequest()->getParams();
        if (!empty($params)) {
            /** @var \Encomage\Stories\Model\Stories $modelStory */
            $modelStory = $this->storiesFactory->create();
            $modelStory->setData($params);
            try {
                $this->storiesRepository->save($modelStory);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Has not saved'));
            }
        } else {
            $this->messageManager->addErrorMessage(__('No data'));
        }
        return $resultRedirect->setPath('*/index/index');
    }
}