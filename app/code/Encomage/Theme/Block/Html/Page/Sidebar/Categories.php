<?php

namespace Encomage\Theme\Block\Html\Page\Sidebar;

use Magento\Framework\View\Element\Template;

class Categories extends \Magento\Framework\View\Element\Template
{
    protected $_categoryHelper;
    protected $_categories = null;
    protected $_json;
    protected $_registry;
    protected $_mainActiveCategoryId = null;
    protected $_activeCategoryPath = null;
    protected $_currentCategory = null;

    public function __construct(
        Template\Context $context,
        \Magento\Catalog\Helper\Category $categoryHelper,
        \Magento\Framework\Serialize\Serializer\Json $json,
        \Magento\Framework\Registry $registry,
        array $data = []
    )
    {
        $this->_categoryHelper = $categoryHelper;
        $this->_json = $json;
        $this->_registry = $registry;
        parent::__construct($context, $data);
    }

    public function getCategories()
    {
        return $this->_categories;
    }

    public function getCurrentCategory()
    {
        return $this->_registry->registry('current_category');
    }

    public function getMainActiveCategoryId()
    {
        return $this->_mainActiveCategoryId;
    }

    public function getJsConfig()
    {
        return $this->_json->serialize(
            [
                'activeMainCategoryId' => $this->_mainActiveCategoryId,
                'activeCategoryPath' => $this->_activeCategoryPath,
                'currentCategoryId' => $this->_currentCategory
            ]
        );
    }

    public function getSubCategoriesHtml(\Magento\Framework\Data\Tree\Node $childCat, bool $addLinkToParentCat = true)
    {
        $children = $childCat->getChildren();
        $html = '';
        $html .= '<ul style="display: none;" data-parent-id="' . $childCat->getId() . '">';
        /** If not top category */
        if ($childCat->hasChildren() && $addLinkToParentCat) {
            $liClass = $this->_isActiveMenuItem($childCat->getId())
            && $childCat->getId() == $this->getCurrentCategory()->getId() ? 'class="active"' : '';
            $html .= '<li data-category-id="' . $childCat->getId() . '" ' . $liClass . '>';
            $html .= '<a data-category-id="' . $childCat->getId()
                . '" href="' . $this->_categoryHelper->getCategoryUrl($childCat) . '">'
                . __('All')
                . '</a>';
            $html .= '</li>';
        }
        foreach ($children as $item) {
            $liClass = $this->_isActiveMenuItem($item->getId()) ? 'class="active"' : '';
            $html .= '<li data-category-id="' . $item->getId() . '" ' . $liClass . '>';
            $linkClasses = 'js-sidebar-category';
            if ($item->hasChildren()) {
                $linkClasses .= ' js-no-link';
            }
            $html .= '<a data-category-id="' . $item->getId()
                . '" class="' . $linkClasses
                . '" href="'
                . $this->_categoryHelper->getCategoryUrl($item) . '">'
                . $item->getName() . '</a>';
            if ($item->hasChildren()) {
                $html .= $this->getSubCategoriesHtml($item);
            }
            $html .= '</li>';
        }
        $html .= '</ul>';
        return $html;
    }

    protected function _isActiveMenuItem(int $catId)
    {
        return ($this->_activeCategoryPath && in_array($catId, $this->_activeCategoryPath));
    }

    protected function _prepareCategories()
    {
        $categories = $this->_categoryHelper->getStoreCategories(false, false, true);
        /** @var \Magento\Framework\Data\Tree\Node $categoryNode */
        foreach ($categories as $categoryNode) {
            if ($categoryNode->hasChildren()) {
                $this->_categories[] = $categoryNode;
            }
        }
        if ($this->_categories) {
            if (!$this->getCurrentCategory()) {
                $category = $this->_categories[0];
                $this->_mainActiveCategoryId = $category->getId();
            } else {
                $path = explode('/', $this->getCurrentCategory()->getPath());
                unset($path[0]);
                unset($path[1]);
                $mainActiveCategoryId = $path[2];
                if (!empty($path)) {
                    $this->_activeCategoryPath = $path;
                }
                $this->_mainActiveCategoryId = $mainActiveCategoryId;
                $this->_currentCategory = $this->getCurrentCategory()->getId();
            }
        }
        return $this;
    }

    protected function _beforeToHtml()
    {
        $this->_prepareCategories();
        return parent::_beforeToHtml();
    }
}