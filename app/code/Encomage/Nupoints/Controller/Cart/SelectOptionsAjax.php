<?php
namespace Encomage\Nupoints\Controller\Cart;

use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Controller\ResultFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Catalog\Block\Product\ImageBuilder;

/**
 * Class SelectOptionsAjax
 * @package Encomage\Nupoints\Controller\Cart
 */
class SelectOptionsAjax extends \Magento\Framework\App\Action\Action
{
    /** @var CustomerSession  */
    protected $customerSession;
    
    /** @var ProductRepositoryInterface  */
    protected $productRepository;
    
    /** @var ImageBuilder  */
    protected $imageBuilder;
    
    /** @var Json  */
    protected $json;

    /**
     * SelectOptionsAjax constructor.
     * @param Context $context
     * @param CustomerSession $customerSession
     * @param ProductRepositoryInterface $productRepository
     * @param ImageBuilder $imageBuilder
     * @param Json $json
     */
    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        ProductRepositoryInterface $productRepository,
        ImageBuilder $imageBuilder,
        Json $json
    )
    {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->productRepository = $productRepository;
        $this->imageBuilder = $imageBuilder;
        $this->json = $json;
    }

    /**
     * @return mixed
     * @throws NotFoundException
     */
    public function execute()
    {
        if (!$this->getRequest()->isAjax()) {
            throw new NotFoundException(__('Incorrect method.'));
        }
        $responseData = [];
        if (!$this->customerSession->isLoggedIn()) {
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $this->getResponse()->setBody($this->json->serialize($responseData))->sendResponse();
        }
        $html = $this->_getOptionHtml();
        return $this->getResponse()->setBody($this->json->serialize(['html' => $html]))->sendResponse();
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
     * @return string
     */
    protected function _getOptionHtml()
    {
        $availableRedeemList = $this->getAvailableRedeemList();
        $html = "<div class=\"redeem-nupoints js-select-custom js-nupoints-rates\"
             data-mage-init='{\"customSelect\":{\"placeholder\":\"" . __('Redeem the following number of points') . "\"}}'>
            <div class=\"js-list\">";
        foreach ($availableRedeemList as $item) {
            $product = $item['product'];

            $html .= '<div class="js-option" ';
            $html .= ($this->getSelectedOption() == $item['nupoints']) ? 'selected="true" ' : ' ';
            $html .= 'data-option-value="' . $item['nupoints'] . '">';

            $html .= '<div class="option-container">';
            if ($product) {
                $image = $this->getImage($product, 'nupoint_related_product');
                $html .= '<div class="image-container">' . $image->toHtml() . '</div>';
            }
            $html .= '<div class="text-container">';
            $html .= '<p>' . __('%1 nuPoints for (฿) %2 off', $item['nupoints'], $item['money']) . '</p>';
            if ($product) {
                $html .= '<p>' . __('Free a product') . ' ' . $product->getName() . '</p>';
            }
            $html .= '</div></div></div>';
        }
        $html .= '</div></div>';
        return $html;
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