<?php

namespace UpMedio\Registry\Service;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Session as CatalogSession;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class GetCurrentProductService.
 */
class GetCurrentProductService
{
    /**
     * @var CatalogSession
     */
    private $catalogSession;
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;
    private $productId;
    private $currentProduct;

    /**
     * GetCurrentProductService constructor.
     *
     * @param CatalogSession                  $catalogSession
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     */
    public function __construct(
        CatalogSession $catalogSession,
        ProductRepositoryInterface $productRepository
    ) {
        $this->catalogSession = $catalogSession;
        $this->productRepository = $productRepository;
    }

    /**
     * @return int|null
     */
    public function getProductId(): ?int
    {
        if (!$this->productId) {
            $productId = $this->catalogSession->getData('last_viewed_product_id');
            $this->productId = $productId ? (int) $productId : null;
        }

        return $this->productId;
    }

    /**
     * @return ProductInterface|null
     */
    public function getProduct(): ?ProductInterface
    {
        if (!$this->currentProduct) {
            $productId = $this->getProductId();
            if (!$productId) {
                return null;
            }
            try {
                $this->currentProduct = $this->productRepository->getById($this->getProductId());
            } catch (NoSuchEntityException $e) {
                return null;
            }
        }

        return $this->currentProduct;
    }
}
