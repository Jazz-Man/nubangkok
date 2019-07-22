<?php
namespace Encomage\Nupoints\Controller\Cart;

use Encomage\Nupoints\Block\Nupoints\SelectorRedeem;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class SelectOptionsAjax
 * @package Encomage\Nupoints\Controller\Cart
 */
class SelectOptionsAjax extends Action
{
    /** @var CustomerSession  */
    protected $customerSession;
    
    /** @var Json  */
    protected $json;

    /**
     * SelectOptionsAjax constructor.
     * @param Context $context
     * @param CustomerSession $customerSession
     * @param Json $json
     */
    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        Json $json
    )
    {
        parent::__construct($context);
        $this->customerSession = $customerSession;
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
        $block = $this->_view->getLayout()
            ->createBlock(SelectorRedeem::class, 'checkout.select.nupoints')
            ->setTemplate('Encomage_Nupoints::nupoints/selector-redeem.phtml');
        $selectorHtml = $block->toHtml();
        return $this->getResponse()->setBody($this->json->serialize(['html' => $selectorHtml]))->sendResponse();
    }
}