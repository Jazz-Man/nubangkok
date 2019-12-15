<?php
namespace Encomage\ErpIntegration\Observer;

use Encomage\ErpIntegration\Helper\ErpApiInvoice;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class QuoteSubmitSuccess
 * @package Encomage\ErpIntegration\Observer
 */
class QuoteSubmitSuccess implements ObserverInterface
{
    /**
     * @var \Encomage\ErpIntegration\Helper\ErpApiInvoice
     */
    private $erpApiInvoice;

    /**
     * QuoteSubmitSuccess constructor.
     *
     * @param \Encomage\ErpIntegration\Helper\ErpApiInvoice $erpApiInvoice
     */
    public function __construct(
        ErpApiInvoice $erpApiInvoice
    )
    {
        $this->erpApiInvoice = $erpApiInvoice;
    }

    /**
     * @param Observer $observer
     *
     * @return void
     * @throws \Exception
     */
    public function execute(Observer $observer)
    {
        $this->erpApiInvoice->createInvoice($observer->getEvent()->getOrder());
    }
}