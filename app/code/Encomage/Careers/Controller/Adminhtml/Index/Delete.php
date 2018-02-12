<?php
namespace Encomage\Careers\Controller\Adminhtml\Index;
use Encomage\Careers\Model\CareersFactory;
use Magento\Backend\App\Action;

class Delete extends Action
{
    /**
     * @var CareersFactory
     */
    private $careersFactory;

    /**
     * Delete constructor.
     * @param Action\Context $context
     * @param CareersFactory $careers
     */
    public function __construct(Action\Context $context, CareersFactory $careers)
    {
        $this->careersFactory = $careers;
        parent::__construct($context);
    }

    /**
     * @return $this
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');

        if (!($contact = $this->careersFactory->create()->load($id))) {
            $this->messageManager->addError(__('Unable to proceed. Please, try again.'));
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('*/*/index', array('_current' => true));
        }
        try{
            $contact->delete();
            $this->messageManager->addSuccess(__('Your contact has been deleted !'));
        } catch (Exception $e) {
            $this->messageManager->addError(__('Error while trying to delete contact: '));
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('*/*/index', array('_current' => true));
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/index', array('_current' => true));
    }
}