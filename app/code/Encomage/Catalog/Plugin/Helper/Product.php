<?php

namespace Encomage\Catalog\Plugin\Helper;

use Magento\Catalog\Helper\Product as Subject;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Registry;
use Encomage\Catalog\Helper\Config as Helper;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\Response\Http as Response;
use Magento\Framework\UrlInterface;
use Magento\Framework\Session\SessionManagerInterface;

/**
 * Class Product
 *
 * @package Encomage\Catalog\Plugin\Helper
 */
class Product
{
    /**
     * @var Configurable
     */
    private $catalogProductTypeConfigurable;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var Http
     */
    private $response;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var Subject
     */
    private $productHelper;

    /**
     * @var SessionManagerInterface
     */
    private $session;

    /**
     * Product constructor.
     *
     * @param Configurable $configurable
     * @param ProductRepository $productRepository
     * @param Registry $registry
     * @param Helper $helper
     * @param CollectionFactory $collectionFactory
     * @param Response $response
     * @param UrlInterface $urlInterface
     * @param Subject $productHelper
     * @param SessionManagerInterface $session
     */
    public function __construct(
        Configurable $configurable,
        ProductRepository $productRepository,
        Registry $registry,
        Helper $helper,
        CollectionFactory $collectionFactory,
        Response $response,
        UrlInterface $urlInterface,
        Subject $productHelper,
        SessionManagerInterface $session
    ) {
        $this->catalogProductTypeConfigurable = $configurable;
        $this->productRepository = $productRepository;
        $this->registry = $registry;
        $this->helper = $helper;
        $this->collectionFactory = $collectionFactory;
        $this->response = $response;
        $this->url = $urlInterface;
        $this->productHelper = $productHelper;
        $this->session = $session;
    }

    /**
     * @param Subject $subject
     * @param callable $proceed
     * @param $productId
     * @param $controller
     * @param $params
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundInitProduct(Subject $subject, callable $proceed, $productId, $controller, $params)
    {
        if ($this->helper->isUseSimpleInsteadConfigurable()) {
            $parentIdByChildIds = $this->catalogProductTypeConfigurable->getParentIdsByChild($productId);
            if (count($parentIdByChildIds)) {
                $product = $this->productRepository->getById($productId);
                $this->registry->register('simple_product', $product);
                if (count($parentIdByChildIds) > 1) {
                    $productId = $this->collectionFactory->create()
                        ->addFieldToFilter('entity_id',
                            ['in' => $parentIdByChildIds])
                        ->addCategoriesFilter(['eq' => $params->getCategoryId()])
                        ->getFirstItem()
                        ->getId();
                } else {
                    $productId = array_shift($parentIdByChildIds);
                }
            } else {
                if ($this->_getProductId()) {
                    $product = $this->productRepository->getById((int)$this->_getProductId());
                    $this->registry->register('simple_product', $product);
                    $this->_unSetProductId();
                }
            }

            $result = $proceed($productId, $controller, $params);
            if(!isset($product)){
                $product =$this->registry->registry('current_product');
            }

            $url = $this->productHelper->getProductUrl($result);
            if ($this->url->getCurrentUrl() !== $url) {
                $this->_setProductId((int)$product->getId());
                $this->response->setRedirect($this->url->getUrl($url));
            }

            return $result;
        }

        return $proceed($productId, $controller, $params);
    }

    /**
     * @param Subject $subject
     * @param $result
     * @param $product
     * @return bool
     */
    public function afterCanShow(Subject $subject, $result, $product)
    {
        if ($this->helper->isUseSimpleInsteadConfigurable()) {
            $result = in_array($product->isVisibleInCatalog(), [
                Visibility::VISIBILITY_IN_SEARCH,
                Visibility::VISIBILITY_IN_CATALOG,
                Visibility::VISIBILITY_BOTH,
                Visibility::VISIBILITY_NOT_VISIBLE,
            ]);
        }

        return $result;
    }

    /**
     * @param int $value
     * @return mixed
     */
    protected function _setProductId(int $value)
    {
        $this->session->start();

        return $this->session->setMessage($value);
    }

    /**
     * @return mixed
     */
    protected function _getProductId()
    {
        $this->session->start();

        return $this->session->getMessage();
    }

    /**
     * @return mixed
     */
    protected function _unSetProductId()
    {
        $this->session->start();

        return $this->session->unsMessage();
    }
}