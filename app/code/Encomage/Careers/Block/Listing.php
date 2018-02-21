<?php
namespace Encomage\Careers\Block;

use \Encomage\Careers\Model\ResourceModel\Careers\CollectionFactory;
use \Magento\Framework\View\Element\Template\Context;

class Listing extends \Magento\Framework\View\Element\Template
{
    const STRING_COUNT_CHARACTERS = 360;
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
        ->addOrder('updated_at', \Magento\Framework\Data\Collection::SORT_ORDER_DESC);
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

    /**
     * @param string $string
     * @return string
     */
    public function getCutLength(string $string)
    {
        $count = iconv_strlen($string);
        if ($count > self::STRING_COUNT_CHARACTERS) {
            $string = iconv_substr(trim($string), 0, self::STRING_COUNT_CHARACTERS - 30) . '... ';
        }
        return $string;
    }
}