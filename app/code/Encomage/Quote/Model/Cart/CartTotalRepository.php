<?php

namespace Encomage\Quote\Model\Cart;

use Magento\Quote\Api;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\CartTotalRepositoryInterface;
use Magento\Catalog\Helper\Product\ConfigurationPool;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Quote\Api\Data\TotalsInterface;
use Magento\Quote\Api\Data\TotalsInterfaceFactory;
use Magento\Quote\Model\Cart\Totals\ItemConverter;
use Magento\Quote\Api\CouponManagementInterface;
use Magento\Quote\Model\Cart\TotalsConverter;

/**
 * Fir for magento issue magento/magento2#12993
 *
 * Cart totals data object.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CartTotalRepository implements CartTotalRepositoryInterface
{
    /**
     * Cart totals factory.
     *
     * @var Api\Data\TotalsInterfaceFactory
     */
    private $totalsFactory;
    /**
     * Quote repository.
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $quoteRepository;
    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    private $dataObjectHelper;
    /**
     * @var ConfigurationPool
     */
    private $itemConverter;
    /**
     * @var CouponManagementInterface
     */
    protected $couponService;
    /**
     * @var \Magento\Quote\Model\Cart\TotalsConverter
     */
    private $totalsConverter;


    /**
     * CartTotalRepository constructor.
     * @param Api\Data\TotalsInterfaceFactory $totalsFactory
     * @param CartRepositoryInterface $quoteRepository
     * @param DataObjectHelper $dataObjectHelper
     * @param CouponManagementInterface $couponService
     * @param \Magento\Quote\Model\Cart\TotalsConverter $totalsConverter
     * @param ItemConverter $converter
     */
    public function __construct(
        TotalsInterfaceFactory $totalsFactory,
        CartRepositoryInterface $quoteRepository,
        DataObjectHelper $dataObjectHelper,
        CouponManagementInterface $couponService,
        TotalsConverter $totalsConverter,
        ItemConverter $converter
    )
    {
        $this->totalsFactory = $totalsFactory;
        $this->quoteRepository = $quoteRepository;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->couponService = $couponService;
        $this->itemConverter = $converter;
        $this->totalsConverter = $totalsConverter;
    }


    /**
     * @param int $cartId
     *
     * @return \Magento\Quote\Api\Data\TotalsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Exception
     */
    public function get($cartId)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        if ($quote->isVirtual()) {
            $addressTotalsData = $quote->getBillingAddress()->getData();
            $addressTotals = $quote->getBillingAddress()->getTotals();
        } else {
            $addressTotalsData = $quote->getShippingAddress()->getData();
            $addressTotals = $quote->getShippingAddress()->getTotals();
        }
        unset($addressTotalsData[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]);
        /** @var \Magento\Quote\Api\Data\TotalsInterface $quoteTotals */
        $quoteTotals = $this->totalsFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $quoteTotals,
            $addressTotalsData,
            TotalsInterface::class
        );
        $items = [];
        foreach ($quote->getAllVisibleItems() as $index => $item) {
            $items[$index] = $this->itemConverter->modelToDataObject($item);
        }
        $calculatedTotals = $this->totalsConverter->process($addressTotals);
        $quoteTotals->setTotalSegments($calculatedTotals);
        $amount = $quoteTotals->getGrandTotal() - $quoteTotals->getTaxAmount();
        $amount = $amount > 0 ? $amount : 0;
        $quoteTotals->setCouponCode($this->couponService->get($cartId));
        $quoteTotals->setGrandTotal($amount);
        $quoteTotals->setItems($items);
        $quoteTotals->setItemsQty($quote->getItemsQty());
        $quoteTotals->setBaseCurrencyCode($quote->getBaseCurrencyCode());
        $quoteTotals->setQuoteCurrencyCode($quote->getQuoteCurrencyCode());
        return $quoteTotals;
    }
}