<?php

namespace Encomage\Catalog\Observer;

use  Encomage\Catalog\Model\ResourceModel\Category\ComingSoon\CollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Encomage\Catalog\Model\Category\ComingSoonProduct;
use Encomage\Catalog\Model\ResourceModel\Category\ComingSoonProduct as ComingSoonResource;
use Magento\Framework\Event\Observer;

/**
 * Class ChangeCategoryProducts
 * @package Encomage\Catalog\Observer
 */
class ChangeCategoryProducts implements \Magento\Framework\Event\ObserverInterface
{

    const COMING_SOON_PRODUCT_TEMPLATE_EMAIL = 'coming_soon_category_product';

    /**
     * @var CollectionFactory
     */
    protected $_comingSoonProductCollection;

    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var ComingSoonProduct
     */
    protected $_comingSoonProduct;

    /**
     * @var ComingSoonResource
     */
    protected $_soonProductResource;

    /**
     * ChangeCategoryProducts constructor.
     * @param ComingSoonResource $soonProductResource
     * @param CollectionFactory $comingSoonProductCollection
     * @param ScopeConfigInterface $scopeConfig
     * @param ComingSoonProduct $comingSoonProduct
     */
    public function __construct(
        ComingSoonResource $soonProductResource,
        CollectionFactory $comingSoonProductCollection,
        ScopeConfigInterface $scopeConfig,
        ComingSoonProduct $comingSoonProduct
    )
    {
        $this->_soonProductResource = $soonProductResource;
        $this->_scopeConfig = $scopeConfig;
        $this->_comingSoonProduct = $comingSoonProduct;
        $this->_comingSoonProductCollection = $comingSoonProductCollection;
    }

    /**
     * @param Observer $observer
     * @return $this|void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     * @throws \Zend_Validate_Exception
     */
    public function execute(Observer $observer)
    {
        $categoryIds = $observer->getProduct()->getCategoryIds();
        $collection = $this->_comingSoonProductCollection->create();
        $collection->addFieldToFilter('category_id', ['id' => $categoryIds]);
        $collection->getSelect()->group('email');
        if (!empty($collection->getItems())) {
            $sender['email'] = $this->_scopeConfig->getValue('trans_email/ident_support/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $sender['name'] = $this->_scopeConfig->getValue('trans_email/ident_support/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $categoryId = [];
            foreach ($collection as $item) {
                if (!\Zend_Validate::is($item->getEmail(), 'EmailAddress')) {
                    $categoryId[] = $item->getCategoryId();
                    continue;
                }
                $this->_comingSoonProduct->sendEmail($sender, $item->getEmail(), self::COMING_SOON_PRODUCT_TEMPLATE_EMAIL);
                $categoryId[] = $item->getCategoryId();
            }
            if (count($categoryId)) {
                $this->_soonProductResource->deleteEmailsByCategoryIds($categoryIds);
            }

        }

        return $this;
    }
}