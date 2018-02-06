<?php
namespace Encomage\Careers\Block;

use \Encomage\Careers\Model\ResourceModel\Careers\CollectionFactory;
use \Magento\Framework\View\Element\Template\Context;

class Listing extends \Magento\Framework\View\Element\Template
{
    /**
     * @var CollectionFactory
     */
    protected $_careersCollectionFactory;

    /**
     * Listing constructor.
     * @param Context $context
     * @param array $data
     * @param CollectionFactory $careersCollectionFactory
     */
    public function __construct(Context $context, array $data, CollectionFactory $careersCollectionFactory)
    {
        $this->_careersCollectionFactory = $careersCollectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * @return \Encomage\Careers\Model\ResourceModel\Careers\Collection
     */
    public function getCareersCollection()
    {
        $collection = $this->_careersCollectionFactory->create();
        return $collection;
    }

    /**
     * @param $id
     * @return string
     */
    public function getUrlView($id)
    {
        return $this->getUrl('careers/view/index', ['id' => $id]);
    }
}