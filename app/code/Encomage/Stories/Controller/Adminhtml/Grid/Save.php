<?php
namespace Encomage\Stories\Controller\Adminhtml\Grid;

use Magento\Backend\App\Action;
use Encomage\Stories\Api\StoriesRepositoryInterface;
use Encomage\Stories\Api\Data\StoriesInterfaceFactory;
use Magento\Framework\App\Cache\StateInterface;
use Magento\Framework\App\Cache\TypeListInterface;

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
     * @var StateInterface
     */
    private $cacheState;
    /**
     * @var TypeListInterface
     */
    private $typeList;

    /**
     * Save constructor.
     * @param Action\Context $context
     * @param StoriesRepositoryInterface $storiesRepository
     * @param StoriesInterfaceFactory $storiesFactory
     * @param StateInterface $cacheState
     * @param TypeListInterface $typeList
     */
    public function __construct(
        Action\Context $context,
        StoriesRepositoryInterface $storiesRepository,
        StoriesInterfaceFactory $storiesFactory,
        StateInterface $cacheState,
        TypeListInterface $typeList
    )
    {
        $this->storiesRepository = $storiesRepository;
        $this->storiesFactory = $storiesFactory;
        $this->cacheState = $cacheState;
        $this->typeList = $typeList;
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

    /**
     * @return $this
     */
    protected function _invalidateCache()
    {
        if ($this->cacheState->isEnabled(\Magento\PageCache\Model\Cache\Type::TYPE_IDENTIFIER)) {
            $this->typeList->invalidate(['full_page']);
        }
        if ($this->cacheState->isEnabled(\Magento\Framework\App\Cache\Type\Block::TYPE_IDENTIFIER)) {
            $this->typeList->invalidate(['block_html']);
        }
        return $this;
    }
}