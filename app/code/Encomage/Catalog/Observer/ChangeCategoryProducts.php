<?php
namespace Encomage\Catalog\Observer ;

class ChangeCategoryProducts implements \Magento\Framework\Event\ObserverInterface
{
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $category = $observer->getCategory();
        $category=$category->getData();
        $categoryId =$category->getId();
        return $this;
    }
}