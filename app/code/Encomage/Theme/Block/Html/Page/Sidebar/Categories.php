<?php

namespace Encomage\Theme\Block\Html\Page\Sidebar;

use Magento\Framework\View\Element\Template;

class Categories extends \Magento\Framework\View\Element\Template
{
    protected $_categoryHelper;
    protected $_categories = null;

    public function __construct(
        Template\Context $context,
        \Magento\Catalog\Helper\Category $categoryHelper,
        array $data = []
    )
    {
        $this->_categoryHelper = $categoryHelper;
        parent::__construct($context, $data);
    }

    public function getCategories()
    {
        if ($this->_categories === null) {
            $categories = $this->_categoryHelper->getStoreCategories(false, false, true);
            /** @var \Magento\Framework\Data\Tree\Node $categoryNode */
            foreach ($categories as $categoryNode) {
                if ($categoryNode->hasChildren()) {
                    $this->_categories[$categoryNode->getId()] = $categoryNode;
                }
            }
        }
        return $this->_categories;
    }
}