<?php

namespace Encomage\Theme\Block\Html\Page\Sidebar;

use function in_array;
use Magento\Catalog\Helper\Category;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template;
use Magento\Catalog\Model\Session as CatalogSession;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

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
    protected $_activeCategoryPath;
    protected $_currentCategory;
    /**
     * @var \Magento\Catalog\Model\Session
     */
    private $catalogSession;
    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    private $categoryRepository;
    /**
     * @var int
     */
    private $categoryId;
    /**
     * @var \Magento\Catalog\Api\Data\CategoryInterface
     */
    private $currentCategory;

    /**
     * Categories constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Catalog\Helper\Category                 $categoryHelper
     * @param \Magento\Framework\Serialize\Serializer\Json     $json
     * @param \Magento\Catalog\Model\Session                   $catalogSession
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
     * @param array                                            $data
     */
    public function __construct(
        Template\Context $context,
        Category $categoryHelper,
        Json $json,
        CatalogSession $catalogSession,
        CategoryRepositoryInterface $categoryRepository,
        array $data = []
    ) {
        $this->_categoryHelper    = $categoryHelper;
        $this->_json              = $json;
        $this->catalogSession     = $catalogSession;
        $this->categoryRepository = $categoryRepository;

        parent::__construct($context, $data);
    }


    /**
     * @return int
     */
    public function getCategoryId(): ?int
    {
        if ( ! $this->categoryId) {
            $currentCategoryId = $this->catalogSession->getData('last_viewed_category_id');
            if ($currentCategoryId) {
                $this->categoryId = (int)$currentCategoryId;
            }
        }

        return $this->categoryId;
    }


    /**
     * @return \Magento\Catalog\Api\Data\CategoryInterface|null
     */
    public function getCategory(): ?CategoryInterface
    {
        if ( ! $this->currentCategory) {
            $categoryId = $this->getCategoryId();
            if ( ! $categoryId) {
                return null;
            }
            try {
                $this->currentCategory = $this->categoryRepository->get($categoryId);
            } catch (NoSuchEntityException $e) {
                return null;
            }
        }

        return $this->currentCategory;
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
    public function getMainActiveCategoryId():? int
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
            'activeCategoryPath'   => $this->_activeCategoryPath,
            'currentCategoryId'    => $this->_currentCategory,
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
        $current_category = $this->getCategory();
        $_children        = $childCat->getChildrenCategories();


        $html = "<ul style='display: none;' data-parent-id='{$childCat->getId()}'>";
        /** If not top category */


        if ($current_category !== null && ($addLinkToParentCat && $childCat->hasChildren())) {

            $active_class = $this->_isActiveMenuItem($childCat->getId()) && $childCat->getId() === $current_category->getId() ? 'class="active"' : '';

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
        return ($this->_activeCategoryPath && in_array($catId, $this->_activeCategoryPath));
    }


    /**
     * @return $this
     */
    protected function _prepareCategories(): ?self
    {
        $current_category = $this->getCategory();

        $categories = $this->_categoryHelper->getStoreCategories();
        /** @var \Magento\Framework\Data\Tree\Node $categoryNode */

        foreach ($categories as $categoryNode) {
            if ($categoryNode->hasChildren()) {
                $this->_categories[] = $categoryNode;
            }
        }


        if ( ! empty($this->_categories)) {
            if ( $current_category !== null) {
                $path = explode('/', $current_category->getPath());

                unset($path[0], $path[1]);

                $mainActiveCategoryId = $path[2];
                if ( ! empty($path)) {
                    $this->_activeCategoryPath = $path;
                }

                $this->_mainActiveCategoryId = $mainActiveCategoryId;
                $this->_currentCategory      = $current_category->getId();
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