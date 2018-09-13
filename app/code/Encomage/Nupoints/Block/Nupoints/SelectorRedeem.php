<?php
namespace Encomage\Nupoints\Block\Nupoints;

use Magento\Framework\View\Element\Template;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\ImageBuilder;

/**
 * Class SelectorRedeem
 * @package Encomage\Nupoints\Block\Nupoints
 */
class SelectorRedeem extends Template
{
    /**
     * @var CustomerSession
     */
    private $customerSession;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;
    /**
     * @var Json
     */
    private $json;
    /**
     * @var \Magento\Catalog\Block\Product\ImageBuilder
     */
    private $imageBuilder;

    /**
     * NupointsRedeem constructor.
     * @param Template\Context $context
     * @param CustomerSession $customerSession
     * @param ScopeConfigInterface $scopeConfig
     * @param ProductRepositoryInterface $productRepository
     * @param ImageBuilder $imageBuilder
     * @param Json $json
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        CustomerSession $customerSession,
        ScopeConfigInterface $scopeConfig,
        ProductRepositoryInterface $productRepository,
        ImageBuilder $imageBuilder,
        Json $json,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->customerSession = $customerSession;
        $this->productRepository = $productRepository;
        $this->scopeConfig = $scopeConfig;
        $this->imageBuilder = $imageBuilder;
        $this->json = $json;
    }

    /**
     * @return array
     */
    public function getAvailableRedeemList()
    {
        $nuPoints = $this->getNupoints()->getNupointsToMoneyRates();
        $result = [];
        foreach ($nuPoints as $money => $rate) {
            $product = (!empty($rate['related_product']))
                ? $product = $this->_loadProductBySku($rate['related_product'])
                : null;
            $result[] = [
                'money' => $money,
                'product' => $product,
                'nupoints' => $rate['from'],
            ];
        }
        return $result;
    }

    /**
     * @return bool
     */
    public function getSelectedOption()
    {
        $nupointsCheckoutData = $this->getNupoints()->getCustomerNupointsCheckoutData();
        if ($nupointsCheckoutData) {
            return $nupointsCheckoutData->getNupointsToRedeem();
        }
        return false;
    }

    /**
     * @return \Encomage\Nupoints\Model\Nupoints
     */
    public function getNupoints()
    {
        return $this->customerSession->getCustomer()->getNupointItem();
    }

    /**
     * @param $product
     * @param $imageId
     * @param array $attributes
     * @return \Magento\Catalog\Block\Product\Image
     */
    public function getImage($product, $imageId, $attributes = [])
    {
        return $this->imageBuilder->setProduct($product)
            ->setImageId($imageId)
            ->setAttributes($attributes)
            ->create();
    }

    /**
     * @param $sku
     * @return bool|\Magento\Catalog\Api\Data\ProductInterface
     */
    protected function _loadProductBySku($sku)
    {
        $product = $this->productRepository->get($sku);
        if ($product) {
            return $product;
        }
        return null;
    }
}