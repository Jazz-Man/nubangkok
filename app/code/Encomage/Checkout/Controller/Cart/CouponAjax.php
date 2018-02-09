<?php

namespace Encomage\Checkout\Controller\Cart;

class CouponAjax extends \Magento\Checkout\Controller\Cart\CouponPost
{
    protected $_resultJsonFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\SalesRule\Model\CouponFactory $couponFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    )
    {
        parent::__construct($context, $scopeConfig, $checkoutSession, $storeManager, $formKeyValidator, $cart, $couponFactory, $quoteRepository);
        $this->_resultJsonFactory = $resultJsonFactory;
    }

    public function execute()
    {
        if (!$this->getRequest()->isAjax()) {
            return parent::execute();
        }

        $response = ['success' => false];
        $resultJson = $this->_resultJsonFactory->create();
        $couponCode = trim($this->getRequest()->getParam('coupon_code'));

        $cartQuote = $this->cart->getQuote();
        $oldCouponCode = $cartQuote->getCouponCode();

        $codeLength = strlen($couponCode);
        if (!$codeLength && !strlen($oldCouponCode)) {
            $resultJson->setData($response);
            return $resultJson;
        }

        try {
            $isCodeLengthValid = $codeLength && $codeLength <= \Magento\Checkout\Helper\Cart::COUPON_CODE_MAX_LENGTH;

            $itemsCount = $cartQuote->getItemsCount();
            if ($itemsCount) {
                $cartQuote->getShippingAddress()->setCollectShippingRates(true);
                $cartQuote->setCouponCode($isCodeLengthValid ? $couponCode : '')->collectTotals();
                $this->quoteRepository->save($cartQuote);
            }

            if ($codeLength) {
                $escaper = $this->_objectManager->get(\Magento\Framework\Escaper::class);
                $coupon = $this->couponFactory->create();
                $coupon->load($couponCode, 'code');
                if (!$itemsCount) {
                    if ($isCodeLengthValid && $coupon->getId()) {
                        $this->_checkoutSession->getQuote()->setCouponCode($couponCode)->save();
                        $response['success'] = true;
                        $response['msg'] =__(
                            'You used coupon code "%1".',
                            $escaper->escapeHtml($couponCode)
                        );
                    } else {
                        $response['msg'] = __(
                            'The coupon code "%1" is not valid.',
                            $escaper->escapeHtml($couponCode)
                        );
                    }
                } else {
                    if ($isCodeLengthValid && $coupon->getId() && $couponCode == $cartQuote->getCouponCode()) {
                        $response['success'] = true;
                        $response['msg'] =  __(
                            'You used coupon code "%1".',
                            $escaper->escapeHtml($couponCode)
                        );
                    } else {
                        $response['msg'] =   __(
                            'The coupon code "%1" is not valid.',
                            $escaper->escapeHtml($couponCode)
                        );
                    }
                }
            } else {
                $response['success'] = true;
                $response['msg'] = __('You canceled the coupon code.');
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $response['success'] = false;
            $response['msg'] = $e->getMessage();
        } catch (\Exception $e) {
            $response['success'] = false;
            $response['msg'] = __('We cannot apply the coupon code.');
        }

        $resultJson->setData($response);
        return $resultJson;
    }
}
