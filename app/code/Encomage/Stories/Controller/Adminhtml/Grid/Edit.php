<?php
namespace Encomage\Stories\Controller\Adminhtml\Grid;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;
use Encomage\Stories\Model\StoriesRepositoryFactory;

/**
 * Class Edit
 * @package Encomage\Stories\Controller\Adminhtml\Grid
 */
class Edit extends Action
{
    /**
     * @var bool|PageFactory
     */
    private $resultPageFactory = false;
    /**
     * @var Registry
     */
    private $registry;
    /**
     * @var StoriesRepositoryFactory
     */
    private $storiesRepository;

    /**
     * Edit constructor.
     * @param Action\Context $context
     * @param Registry $registry
     * @param StoriesRepositoryFactory $storiesRepository
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Action\Context $context,
        Registry $registry,
        StoriesRepositoryFactory $storiesRepository,
        PageFactory $resultPageFactory
    )
    {
        $this->storiesRepository = $storiesRepository;
        $this->registry = $registry;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('entity_id');
        $this->registry->register('stories', $this->_initStory($id));
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Encomage_Stories::stories');
        $resultPage->getConfig()->getTitle()->prepend((__('Story Editor')));
        return $resultPage;
    }

    /**
     * @param $id
     * @return null|object
     */
    protected function _initStory($id)
    {
        $story = null;
        if ($id) {
            $stories = $this->storiesRepository->create();
            $story = $stories->getById($id);
        }
        return $story;
    }
}