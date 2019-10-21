<?php

namespace Encomage\Theme\Block\Html\Page\Sidebar;

use Encomage\Theme\Helper\HtmlAttributes;
use Magento\Catalog\Helper\Category as CategoryHelper;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template;
use UpMedio\Registry\Service\GetCurrentCategoryService;

/**
 * Class Categories.
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
     * @var \Encomage\Theme\Helper\HtmlAttributes
     */
    private $htmlAttributes;

    /**
     * Categories constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context    $context
     * @param \Magento\Catalog\Helper\Category                    $categoryHelper
     * @param \UpMedio\Registry\Service\GetCurrentCategoryService $currentCategoryService
     * @param \Encomage\Theme\Helper\HtmlAttributes               $htmlAttributes
     * @param \Magento\Framework\Serialize\Serializer\Json        $json
     * @param array                                               $data
     */
    public function __construct(
        Template\Context $context,
        CategoryHelper $categoryHelper,
        GetCurrentCategoryService $currentCategoryService,
        HtmlAttributes $htmlAttributes,
        Json $json,
        array $data = []
    ) {
        $this->_categoryHelper = $categoryHelper;
        $this->_json = $json;

        $this->currentCategory = $currentCategoryService->getCategory();
        $this->htmlAttributes = $htmlAttributes;

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
            'activeCategoryPath' => $this->activeCategoryPath,
            'currentCategoryId' => $this->currentCategoryId,
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

        $addLinkToAll = $addLinkToParentCat && $childCat->hasChildren();

        $html = "<ul style='display: none;' data-parent-id='{$childCat->getId()}'>";
        /* If not top category */

        if ($addLinkToAll) {
            $active_class = '';

            if (null !== $this->currentCategory) {
                $active_class = $this->_isActiveMenuItem($childCat->getId()) && $childCat->getId() === $this->currentCategory->getId() ? 'active' : '';
            }

            $itemAttibutes = [
                'data-category-id' => $childCat->getId(),
                'class' => $active_class,
            ];

            $linkAttibutes = [
                'data-category-id' => $childCat->getId(),
                'href' => $this->_categoryHelper->getCategoryUrl($childCat),
            ];

            $html .= "<li {$this->htmlAttributes->getAttributesHtml($itemAttibutes)}>";

            $html .= "<a {$this->htmlAttributes->getAttributesHtml($linkAttibutes)}>";
            $html .= __('All');
            $html .= '</a>';

            $html .= '</li>';
        }

        foreach ($_children as $category) {
            $has_children = $category->hasChildren();

            $itemAttibutes = [
                'data-category-id' => $category->getId(),
                'class' => $this->_isActiveMenuItem($category->getId()) ? 'active' : '',
            ];

            $html .= "<li {$this->htmlAttributes->getAttributesHtml($itemAttibutes)}>";

            $linkAttibutes = [
                'data-category-id' => $category->getId(),
                'href' => $this->_categoryHelper->getCategoryUrl($category),
                'class' => [
                    'js-sidebar-category',
                ],
            ];

            if ($has_children) {
                $linkAttibutes['class'][] = 'js-no-link';
            }

            $html .= "<a {$this->htmlAttributes->getAttributesHtml($linkAttibutes)}>{$category->getName()}</a>";

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
        return $this->activeCategoryPath && \in_array($catId, $this->activeCategoryPath);
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

        if (!empty($this->_categories)) {
            if (null !== $this->currentCategory) {
                $path = explode('/', $this->currentCategory->getPath());

                unset($path[0], $path[1]);

                $mainActiveCategoryId = $path[2];
                if (!empty($path)) {
                    $this->activeCategoryPath = $path;
                }

                $this->_mainActiveCategoryId = $mainActiveCategoryId;
                $this->currentCategoryId = $this->currentCategory->getId();
            } else {
                $category = $this->_categories[0];
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
