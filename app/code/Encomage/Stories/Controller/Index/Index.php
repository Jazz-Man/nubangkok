<?php
namespace Encomage\Stories\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    /**
     * @var PageFactory
     */
    private $pageFactory;
    /**
     * @var Session
     */
    private $customerSession;

    /**
     * Index constructor.
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
        if ($this->isCustomerLogged()) {
            return $this->pageFactory->create();
        }
        $this->_forward('noroute');
    }

    /**
     * @return bool
     */
    protected function isCustomerLogged()
    {
        return $this->customerSession->isLoggedIn();
    }
}