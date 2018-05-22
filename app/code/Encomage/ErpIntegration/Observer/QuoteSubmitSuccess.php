<?php
namespace Encomage\ErpIntegration\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Encomage\ErpIntegration\Model\Api\Invoice as ApiInvoice;
use Magento\Framework\App\Response\RedirectInterface;
use \Magento\Framework\Controller\Result\RedirectFactory;
/**
 * Class QuoteSubmitSuccess
 * @package Encomage\ErpIntegration\Observer
 */
class QuoteSubmitSuccess implements ObserverInterface
{
    /**
     * @var ApiInvoice
     */
    private $apiInvoice;
    /**
     * @var RedirectInterface
     */
    private $redirect;

    /**
     * QuoteSubmitSuccess constructor.
     * @param ApiInvoice $apiInvoice
     */
    public function __construct(ApiInvoice $apiInvoice, RedirectFactory $redirectFactory)
    {
        $this->apiInvoice = $apiInvoice;
        $this->redirect = $redirectFactory;
    }

    /**
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $this->apiInvoice->createInvoice($observer->getEvent()->getOrder());
    }
}