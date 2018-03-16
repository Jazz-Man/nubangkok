<?php
namespace Encomage\Stories\Block;

use Magento\Framework\View\Element\Template;
use Magento\Customer\Model\Session;

class Stories extends Template
{
    /**
     * @var Session
     */
    protected $customerSession;
    
    /**
     * YourStories constructor.
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
     * @return bool
     */
    public function isCustomerLogged()
    {
        return $this->customerSession->isLoggedIn();
    }

    /**
     * @return string
     */
    public function getAddUrl()
    {
        return $this->getUrl('stories/yourstories/add');
    }
}