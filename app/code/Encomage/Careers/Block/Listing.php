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
        $collection->addFieldToFilter('status', ['eq' => \Encomage\Careers\Model\Careers\Source\Status::STATUS_ENABLED])
        ->addOrder('position', \Magento\Framework\Data\Collection::SORT_ORDER_ASC);
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

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getVideoCmsBlock()
    {
        return $this->getLayout()
            ->createBlock('Magento\Cms\Block\Block')
            ->setBlockId('career-image-video-listing-page')
            ->toHtml();
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getImageCmsBlock()
    {
        return $this->getLayout()
            ->createBlock('Magento\Cms\Block\Block')
            ->setBlockId('career-images-listing-page')
            ->toHtml();
    }
}