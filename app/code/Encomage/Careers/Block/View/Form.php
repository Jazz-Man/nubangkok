<?php
namespace Encomage\Careers\Block\View;

use \Encomage\Careers\Model\ResourceModel\Careers\CollectionFactory;
use Magento\Framework\View\Element\Template;
use \Magento\Framework\View\Element\Template\Context;
use \Magento\Framework\Registry;

/**
 * Class Form
 *
 * @package Encomage\Careers\Block\View
 */
class Form extends Template
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

    /**
     * @return string
     */
    public function getFormAction()
    {
        return $this->getUrl('careers/view/sendEmail');
    }
}