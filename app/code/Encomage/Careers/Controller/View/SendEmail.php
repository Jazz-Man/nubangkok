<?php

namespace Encomage\Careers\Controller\View;

use \Magento\Framework\App\Action\Context;
use \Magento\Framework\View\Result\PageFactory;

class SendEmail extends \Magento\Framework\App\Action\Action
{
    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * SendEmail constructor.
     * @param Context $context
     * @param PageFactory $pageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $pageFactory
    )
    {
        $this->pageFactory = $pageFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $param = $this->getRequest()->getParams();
    }
}