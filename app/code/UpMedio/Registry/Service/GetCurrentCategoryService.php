<?php

namespace UpMedio\Registry\Service;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\Session as CatalogSession;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class GetCurrentCategoryService.
 */
class GetCurrentCategoryService
{
    /**
     * @var CatalogSession
     */
    private $catalogSession;
    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * Current Category ID.
     *
     * @var int|null
     */
    private $categoryId;

    /**
     * Current Category.
     *
     * @var CategoryInterface
     */
    private $currentCategory;

    /**
     * GetCurrentCategoryService constructor.
     *
     * @param CatalogSession                  $catalogSession
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
        CatalogSession $catalogSession,
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->catalogSession = $catalogSession;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @return int|null
     */
    public function getCategoryId(): ?int
    {
        if (!$this->categoryId) {
            $currentCategoryId = $this->catalogSession->getData('last_viewed_category_id');
            if ($currentCategoryId) {
                $this->categoryId = (int) $currentCategoryId;
            }
        }

        return $this->categoryId;
    }

    /**
     * @return CategoryInterface|null
     */
    public function getCategory(): ?CategoryInterface
    {
        if (!$this->currentCategory) {
            $categoryId = $this->getCategoryId();
            if (!$categoryId) {
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
}
