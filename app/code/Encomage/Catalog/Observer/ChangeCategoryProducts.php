<?php

namespace Encomage\Catalog\Observer;

use  Encomage\Catalog\Model\ResourceModel\Category\ComingSoon\CollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Encomage\Catalog\Model\Category\ComingSoonProduct;
use Encomage\Catalog\Model\ResourceModel\Category\ComingSoonProduct as ComingSoonResource;

class ChangeCategoryProducts implements \Magento\Framework\Event\ObserverInterface
{

    const COMING_SOON_PRODUCT_TEMPLATE_EMAIL = 'coming_soon_category_product';
    private $_comingSoonProductCollection;
    private $_scopeConfig;
    private $_comingSoonProduct;
    private $_soonProductResource;

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
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $category = $observer->getCategory();
        $collection = $this->_comingSoonProductCollection->create();
        $collection->addFieldToFilter('category_id', ['eq' => $category->getId()]);
        $collection->getSelect()->group('email');
        $collection->getItems();
        if (!empty($collection)) {
            $sender['email'] = $this->_scopeConfig->getValue('trans_email/ident_support/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $sender['name'] = $this->_scopeConfig->getValue('trans_email/ident_support/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            foreach ($collection as $item) {
                $this->_comingSoonProduct->sendEmail($sender, $item->getEmail(), self::COMING_SOON_PRODUCT_TEMPLATE_EMAIL);
            }
            $this->_soonProductResource->deleteEmailsByCategoryId($category->getId());

        }
        return $this;
    }
}