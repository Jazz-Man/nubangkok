<?php
namespace Encomage\Stories\Block\Stories;

use Magento\Framework\View\Element\Template;
use Magento\Customer\Model\Session;
use Encomage\Stories\Model\ResourceModel\Stories\CollectionFactory;

class YourStories extends Template
{
    /**
     * @var Session
     */
    protected $customerSession;
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * YourStories constructor.
     * @param Template\Context $context
     * @param Session $customerSession
     * @param CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Session $customerSession,
        CollectionFactory $collectionFactory,
        array $data
    )
    {
        $this->collectionFactory = $collectionFactory;
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

    public function storiesCollection()
    {
        //todo: Create with repository
        $stories = $this->collectionFactory->create()->load();
        return $stories;
    }
}