<?php
namespace Encomage\Careers\Block\View;

use \Encomage\Careers\Model\ResourceModel\Careers\CollectionFactory;
use \Magento\Framework\View\Element\Template\Context;
use \Magento\Framework\Registry;

class Form extends \Magento\Framework\View\Element\Template
{
    /**
     * @var CollectionFactory
     */
    private $careersCollectionFactory;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * Form constructor.
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
        $this->careersCollectionFactory = $careersCollectionFactory;
        $this->registry;
        parent::__construct($context, $data);
    }

    /**
     * @return bool|mixed
     */
    public function getCareer()
    {
        $career = $this->registry->registry('current_career');
        return ($career) ? $career : false;
    }

    public function getFormAction()
    {
        return $this->getUrl('careers/view/sendEmail');
    }
}