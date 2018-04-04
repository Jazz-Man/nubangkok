<?php
namespace Encomage\Stories\Controller\Yourstories;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Customer\Model\Session;

class Add extends Action
{
    /**
     * @var PageFactory
     */
    private $pageFactory;
    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * Add constructor.
     * @param Context $context
     * @param Session $customerSession
     * @param PageFactory $pageFactory
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        PageFactory $pageFactory
    )
    {
        $this->customerSession = $customerSession;
        $this->pageFactory = $pageFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if (!$this->_isCustomerLogged()) {
            $this->messageManager->addErrorMessage(__('You are not authorized.'));
            $this->_redirect('stories');
        }
        return $this->pageFactory->create();
    }

    /**
     * @return bool
     */
    protected function _isCustomerLogged()
    {
        return $this->customerSession->isLoggedIn();
    }
}