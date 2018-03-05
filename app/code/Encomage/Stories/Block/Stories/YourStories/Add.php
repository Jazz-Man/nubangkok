<?php
namespace Encomage\Stories\Block\Stories\YourStories;

use Magento\Framework\View\Element\Template;
use Magento\Customer\Model\Session;

class Add extends Template
{
    /**
     * @var Session
     */
    private $customerSession;

    /**
     * Add constructor.
     * @param Template\Context $context
     * @param Session $customerSession
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Session $customerSession,
        array $data
    )
    {
        $this->customerSession = $customerSession;
        parent::__construct($context, $data);
    }

    /**
     * @return int|null
     */
    public function getCustomerId()
    {
        return $this->customerSession->getCustomerId();
    }
}