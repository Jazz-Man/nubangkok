<?php
namespace Encomage\Careers\Block;

use \Encomage\Careers\Model\ResourceModel\Careers\CollectionFactory;
use \Magento\Framework\View\Element\Template\Context;
use \Magento\Framework\Registry;

class View extends \Magento\Framework\View\Element\Template
{
    /**
     * @var CollectionFactory
     */
    private $_careersCollectionFactory;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * View constructor.
     * @param Context $context
     * @param array $data
     * @param CollectionFactory $careersCollectionFactory
     * @param Registry $registry
     */
    public function __construct(
        Context $context,
        array $data,
        CollectionFactory $careersCollectionFactory,
        Registry $registry
    )
    {
        $this->registry = $registry;
        $this->_careersCollectionFactory = $careersCollectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * @return \Encomage\Careers\Model\ResourceModel\Careers\Collection
     */
    public function getCareer()
    {
        $career = $this->registry->registry('current_career');
        return ($career) ? $career : false;
    }
}