<?php

namespace Encomage\Theme\Block\Html\Page\Sidebar;

use Magento\Catalog\Helper\Category as CategoryHelper;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template;
use UpMedio\Registry\Service\GetCurrentCategoryService;

/**
 * Class Categories
 *
 * @package Encomage\Theme\Block\Html\Page\Sidebar
 */
class Categories extends Template
{

    protected $_categoryHelper;
    /**
     * @var \Magento\Catalog\Model\Category[]
     */
    protected $_categories = [];
    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $_json;
    /**
     * @var int
     */
    protected $_mainActiveCategoryId;
    protected $activeCategoryPath;

    /**
     * @var int|null
     */
    protected $currentCategoryId;

    /**
     * @var \Magento\Catalog\Api\Data\CategoryInterface|null
     */
    private $currentCategory;

    /**
     * Categories constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context    $context
     * @param \Magento\Catalog\Helper\Category                    $categoryHelper
     * @param \UpMedio\Registry\Service\GetCurrentCategoryService $currentCategoryService
     * @param \Magento\Framework\Serialize\Serializer\Json        $json
     * @param array                                               $data
     */
    public function __construct(
        Template\Context $context,
        CategoryHelper $categoryHelper,
        GetCurrentCategoryService $currentCategoryService,
        Json $json,
        array $data = []
    ) {
        $this->_categoryHelper        = $categoryHelper;
        $this->_json                  = $json;

        $this->currentCategory = $currentCategoryService->getCategory();

        parent::__construct($context, $data);
    }


    /**
     * @return \Magento\Catalog\Model\Category[]
     */
    public function getCategories(): array
    {

        return $this->_categories;
    }


    /**
     * @return int
     */
    public function getMainActiveCategoryId(): ?int
    {
        return $this->_mainActiveCategoryId;
    }

    /**
     * @return bool|false|string
     */
    public function getJsConfig()
    {
        return $this->_json->serialize([
            'activeMainCategoryId' => $this->_mainActiveCategoryId,
            'activeCategoryPath'   => $this->activeCategoryPath,
            'currentCategoryId'    => $this->currentCategoryId,
        ]);
    }

    /**
     * @param \Magento\Catalog\Model\Category $childCat
     * @param bool                            $addLinkToParentCat
     *
     * @return string
     */
    public function getSubCategoriesHtml($childCat, bool $addLinkToParentCat = true): string
    {

        $_children = $childCat->getChildrenCategories();


        $html = "<ul style='display: none;' data-parent-id='{$childCat->getId()}'>";
        /** If not top category */

        if ($this->currentCategory !== null && ($addLinkToParentCat && $childCat->hasChildren())) {

            $active_class = $this->_isActiveMenuItem($childCat->getId()) && $childCat->getId() === $this->currentCategory->getId() ? 'class="active"' : '';

            $html .= "<li data-category-id='{$childCat->getId()}' {$active_class}>";

            $html .= "<a data-category-id='{$childCat->getId()}' href='{$this->_categoryHelper->getCategoryUrl($childCat)}'>";
            $html .= __('All');
            $html .= '</a>';
            $html .= '</li>';
        }


        foreach ($_children as $category) {
            $active_class = $this->_isActiveMenuItem($category->getId()) ? 'class="active"' : '';
            $has_children = $category->hasChildren();

            $html        .= "<li data-category-id='{$category->getId()}' {$active_class}>";
            $linkClasses = 'js-sidebar-category';
            if ($has_children) {
                $linkClasses .= ' js-no-link';
            }

            $html .= "<a data-category-id='{$category->getId()}' class='{$linkClasses}' href='{$this->_categoryHelper->getCategoryUrl($category)}'>{$category->getName()}</a>";

            if ($has_children) {
                $html .= $this->getSubCategoriesHtml($category);
            }

            $html .= '</li>';
        }

        $html .= '</ul>';

        return $html;
    }

    /**
     * @param int $catId
     *
     * @return bool
     */
    protected function _isActiveMenuItem(int $catId): bool
    {
        return ($this->activeCategoryPath && in_array($catId, $this->activeCategoryPath));
    }


    /**
     * @return $this
     */
    protected function _prepareCategories(): ?self
    {
        $categories = $this->_categoryHelper->getStoreCategories();
        /** @var \Magento\Framework\Data\Tree\Node $categoryNode */

        foreach ($categories as $categoryNode) {
            if ($categoryNode->hasChildren()) {
                $this->_categories[] = $categoryNode;
            }
        }


        if ( ! empty($this->_categories)) {
            if ($this->currentCategory !== null) {
                $path = explode('/', $this->currentCategory->getPath());

                unset($path[0], $path[1]);

                $mainActiveCategoryId = $path[2];
                if ( ! empty($path)) {

                    $this->activeCategoryPath = $path;
                }

                $this->_mainActiveCategoryId = $mainActiveCategoryId;
                $this->currentCategoryId     = $this->currentCategory->getId();
            } else {
                $category                    = $this->_categories[0];
                $this->_mainActiveCategoryId = $category->getId();

            }
        }

        return $this;
    }


    /**
     * @return \Magento\Framework\View\Element\Template
     */
    protected function _beforeToHtml()
    {
        $this->_prepareCategories();

        return parent::_beforeToHtml();
    }
}