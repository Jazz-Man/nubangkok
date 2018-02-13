<?php
namespace Encomage\Careers\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Encomage\Careers\Model\ResourceModel\Careers\CollectionFactory;
use Encomage\Careers\Model\ResourceModel\Careers as CareersResource;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;

class MassDelete extends \Magento\Backend\App\Action
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
     * @var CareersResource
     */
    private $careersResource;

    /**
     * MassDelete constructor.
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param CareersResource $careersResource
     */
    public function __construct(
        Context $context, 
        Filter $filter, 
        CollectionFactory $collectionFactory, 
        CareersResource $careersResource
    )
    {
        $this->careersResource = $careersResource;
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
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
            $this->careersResource->delete($collection->fetchItem());
            $this->messageManager->addSuccessMessage(__('A total of %1 element(s) have been deleted.', $collectionSize));
        } catch (\Exception $e) {
            throw new LocalizedException(__($e));
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/index');
    }

    /**
     * is action allowed
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Prince_PincodeChecker::view');
    }
}