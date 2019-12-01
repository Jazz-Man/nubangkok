<?php

namespace Encomage\Checkout\Controller\Cart;

use Exception;
use Magento\Checkout\Helper\Cart;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Locale\ResolverInterface;
use Psr\Log\LoggerInterface;
use Zend_Filter_LocalizedToNormalized;

/**
 * Class Add
 *
 * @package Encomage\Checkout\Controller\Cart
 */
class Add extends \Magento\Checkout\Controller\Cart\Add
{

    /**
     * Add product to shopping cart action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        if ( ! $this->_formKeyValidator->validate($this->getRequest())) {
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }

        $params = $this->getRequest()->getParams();

        try {
            if (isset($params['qty'])) {
                $filter        = new Zend_Filter_LocalizedToNormalized([
                        'locale' => $this->_objectManager->get(ResolverInterface::class)->getLocale(),
                    ]);
                $params['qty'] = $filter->filter($params['qty']);
            }

            $product = $this->_initProduct();
            $related = $this->getRequest()->getParam('related_product');
            if ( ! $product) {
                return $this->goBack();
            }

            $this->cart->addProduct($product, $params);
            if ( ! empty($related)) {
                $this->cart->addProductsByIds(explode(',', $related));
            }

            $this->cart->save();
            $this->_eventManager->dispatch('checkout_cart_add_product_complete',
                ['product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse()]);

            if ( ! $this->_checkoutSession->getNoCartRedirect(true)) {
                return $this->goBack(null, $product);
            }

        } catch (LocalizedException $e) {
            if ($this->_checkoutSession->getUseNotice(true)) {
                $this->messageManager->addNotice($this->_objectManager->get(Escaper::class)
                                                                      ->escapeHtml($e->getMessage()));
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->messageManager->addError($this->_objectManager->get(Escaper::class)->escapeHtml($message));
                }
            }
            $url = $this->_checkoutSession->getRedirectUrl(true);
            if ( ! $url) {
                $cartUrl = $this->_objectManager->get(Cart::class)->getCartUrl();
                $url     = $this->_redirect->getRedirectUrl($cartUrl);
            }

            return $this->goBack($url);
        } catch (Exception $e) {
            $this->messageManager->addException($e, __('We can\'t add this item to your shopping cart right now.'));
            $this->_objectManager->get(LoggerInterface::class)->critical($e);

            return $this->goBack();
        }
    }
}