<?php

namespace Encomage\Careers\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Encomage\Careers\Model\CareersFactory;
use Encomage\Careers\Model\ResourceModel\Careers as CareersResource;
use Magento\Framework\App\Cache\StateInterface;
use Magento\Framework\App\Cache\TypeListInterface;

class Save extends Action
{
    /**
     * @var CareersFactory
     */
    private $careersFactory;

    /**
     * @var CareersResource
     */
    private $careersResource;

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
     * @param CareersResource $careersResource
     * @param CareersFactory $careersFactory
     * @param TypeListInterface $typeList
     * @param StateInterface $cacheState
     */
    public function __construct(
        Action\Context $context,
        CareersResource $careersResource,
        CareersFactory $careersFactory,
        TypeListInterface $typeList,
        StateInterface $cacheState
    )
    {
        $this->careersFactory = $careersFactory;
        $this->careersResource = $careersResource;
        $this->cacheState = $cacheState;
        $this->typeList = $typeList;
        parent::__construct($context);
    }

    /**
     * @return $this|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $params = $this->getRequest()->getParams();
        if (!empty($params)) {
            $model = $this->careersFactory->create();
            try {
                if (!empty($params['id'])) {
                    $this->careersResource->load($model, $params['id']);
                } else {
                    unset($params['id']);
                }
                $params['position'] = 0;
                $model->setData($params);
                //TODO: will be better add validation
                $this->careersResource->save($model);
                $this->_invalidateCache();
                $this->messageManager->addSuccessMessage(__('Vacancy has been saved'));
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Some errors.'));
            }
        }
        return $resultRedirect->setPath('*/*/index');
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