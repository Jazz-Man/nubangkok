<?php
namespace Encomage\Careers\Controller\Adminhtml\Index;

use \Encomage\Careers\Model\CareersFactory;
use \Encomage\Careers\Model\ResourceModel\Careers as CareersResource;
use \Magento\Backend\App\Action;

class Delete extends Action
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
     * Delete constructor.
     * @param Action\Context $context
     * @param CareersFactory $careers
     * @param CareersResource $careersResource
     */
    public function __construct(
        Action\Context $context,
        CareersFactory $careers,
        CareersResource $careersResource
    )
    {
        $this->careersResource = $careersResource;
        $this->careersFactory = $careers;
        parent::__construct($context);
    }

    /**
     * @return $this
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $careersModel = $this->careersFactory->create();
        $vacancy = $this->careersResource->load($careersModel, $id);
        if (!($vacancy)) {
            $this->messageManager->addErrorMessage(__('Unable to proceed. Please, try again.'));
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('*/*/index', array('_current' => true));
        }
        
        try {
            $this->careersResource->delete($vacancy);
            $this->messageManager->addSuccessMessage(__('Your contact has been deleted !'));
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage(__('Error while trying to delete contact: '));
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('*/*/index', array('_current' => true));
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/index', array('_current' => true));
    }
}