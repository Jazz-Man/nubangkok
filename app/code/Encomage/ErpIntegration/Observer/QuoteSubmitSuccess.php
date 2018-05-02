<?php
namespace Encomage\ErpIntegration\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Encomage\ErpIntegration\Model\Api\Invoice as ApiInvoice;

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
     * QuoteSubmitSuccess constructor.
     * @param ApiInvoice $apiInvoice
     */
    public function __construct(ApiInvoice $apiInvoice)
    {
        $this->apiInvoice = $apiInvoice;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $this->apiInvoice->createInvoice($observer->getEvent()->getOrder());
    }
}