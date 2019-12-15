<?php

namespace Encomage\ErpIntegration\Helper;

use Encomage\ErpIntegration\Logger\Logger;
use Encomage\ErpIntegration\Model\Api\ErpCreateInvoiceResponse;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Model\ResourceModel\Order as OrderResource;
use Psr\Http\Message\ResponseInterface;

/**
 * Class ErpApiInvoice.
 */
class ErpApiInvoice extends AbstractHelper
{
    /**
     * @var \Magento\Sales\Model\OrderRepository
     */
    private $orderRepository;
    /**
     * @var \Encomage\ErpIntegration\Helper\ErpApiClient
     */
    private $erpApiClient;
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order
     */
    private $orderResource;
    /**
     * @var \Encomage\ErpIntegration\Helper\ErpApiCustomer
     */
    private $erpApiCustomer;

    /**
     * @var \Encomage\ErpIntegration\Helper\ErpApiInvoiceAddress
     */
    private $erpApiInvoiceAddress;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * ErpApiInvoice constructor.
     *
     * @param \Magento\Framework\App\Helper\Context                $context
     * @param \Magento\Sales\Model\OrderRepository                 $orderRepository
     * @param \Magento\Sales\Model\ResourceModel\Order             $orderResource
     * @param \Encomage\ErpIntegration\Helper\ErpApiInvoiceAddress $erpApiInvoiceAddress
     * @param \Encomage\ErpIntegration\Logger\Logger               $logger
     * @param \Encomage\ErpIntegration\Helper\ErpApiClient         $erpApiClient
     * @param \Encomage\ErpIntegration\Helper\ErpApiCustomer       $erpApiCustomer
     */
    public function __construct(
        Context $context,
        OrderRepository $orderRepository,
        OrderResource $orderResource,
        ErpApiInvoiceAddress $erpApiInvoiceAddress,
        Logger $logger,
        ErpApiClient $erpApiClient,
        ErpApiCustomer $erpApiCustomer
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderResource = $orderResource;
        $this->erpApiClient = $erpApiClient;
        $this->erpApiCustomer = $erpApiCustomer;
        $this->erpApiInvoiceAddress = $erpApiInvoiceAddress;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * @param int|\Magento\Sales\Model\Order|\Magento\Sales\Api\Data\OrderInterface $order
     */
    public function createInvoice($order)
    {
        $invoiceData = $this->prepareInvoicePostData($order);

        if (!empty($invoiceData)) {
            $this->logger->info('Start ERP CreateInvoice');

            $clientHandler = $this->erpApiClient->getClient()->getConfig('handler');

            $self = $this;

            $tapMiddleware = Middleware::tap(static function (Request $request) use ($self) {
                /** @var \GuzzleHttp\Psr7\Uri $uri */
                $uri = $request->getUri();

                $Body = $self->erpApiClient->jsonDecode($request->getBody(), true);

                $self->logger->info('CreateInvoice Request Uri:', [(string) $uri]);
                $self->logger->info('CreateInvoice Request Body:', $Body);
            });

            $promise = $this->erpApiClient->postJsonData('CreateInvoice', $invoiceData, [
                'handler' => $tapMiddleware($clientHandler),
            ])->then(static function (
                ResponseInterface $res
            ) use ($self, $order) {
                try {
                    $result = $self->erpApiClient->parseBody($res);

                    $erpInvoice = new ErpCreateInvoiceResponse($result);

                    $self->logger->info('CreateInvoice Response', (array) $result);
                    if ($erpInvoice->isValid()) {
                        $self->updateOrderData($order, $erpInvoice);
                    }
                } catch (\Exception $exception) {
                }

                return false;
            });

            $promise->wait();
        }
    }

    /**
     * @param int|\Magento\Sales\Model\Order|\Magento\Sales\Api\Data\OrderInterface $order
     *
     * @return array|bool
     */
    public function prepareInvoicePostData($order)
    {
        $result = false;

        try {
            if (is_numeric($order)) {
                $order = $this->orderRepository->get($order);
            }

            if (null !== $order && \in_array($order->getStatus(), ['canceled', 'closed', 'fraud'])) {
                return $result;
            }

            $CustomerCode = $this->erpApiCustomer->getCustomerErpCode($order->getCustomerId());

            $order_data = [
                'CustomerCode' => $CustomerCode,
                'customerTaxid' => 'tax1',
                'customerBranchno' => 'Online',
                'branchCode' => 'ON',
                'salespersonCode' => 'admin',
                'currency' => 'THB',
            ];

            /** @var \Magento\Sales\Model\Order\Address $shippingAddress */
            $shippingAddress = $order->getShippingAddress();

            if (null !== $shippingAddress) {
                $order_data['customerAddress'] = $this->erpApiInvoiceAddress->prepareAddress($shippingAddress);
                $order_data['customerTelephone'] = $shippingAddress->getTelephone();
                $order_data['customerName'] = $shippingAddress->getName();
            }

            $items = [];

            foreach ($order->getItems() as $item) {
                if ('simple' === $item->getProductType()) {
                    $parentItem = $item->getParentItem();

                    if (null !== $parentItem) {
                        $discount = $parentItem->getDiscountPercent();
                    } else {
                        $discount = 0;
                    }

                    $discount = number_format($discount);

                    $items[] = [
                        'productCode' => $item->getSku(),
                        'barcode' => $item->getSku(),
                        'quantity' => (string) abs($item->getQtyOrdered()),
                        'warehouseCode' => $this->erpApiClient->getWarehouseCode(),
                        'discountText' => "{$discount}%",
                    ];
                }
            }

            if ($order->getRedeemAmount()) {
                $items[] = [
                    'productCode' => "Redeem {$order->getRedeemAmount()}",
                    'quantity' => 1,
                    'warehouseCode' => $this->erpApiClient->getWarehouseCode(),
                ];
            }

            $order_data['lineItems'] = $items;

            $paymentInfo = [];

            /** @var \Magento\Sales\Model\Order\Payment $payment */
            $payment = $order->getPayment();

            $paymentInfo[] = [
                'paymentMethodCode' => $payment->getMethod(),
                'amount' => number_format($order->getGrandTotal(), 2, '.', ''),
            ];

            $order_data['linePayments'] = $paymentInfo;

            return ['Order' => $order_data];
        } catch (\Exception $e) {
            $this->logger->error("ERP CreateInvoice ERROR: {$e->getMessage()}");
        }

        return $result;
    }

    /**
     * @param \Magento\Sales\Model\Order                                  $order $order
     * @param \Encomage\ErpIntegration\Model\Api\ErpCreateInvoiceResponse $result
     */
    public function updateOrderData($order, ErpCreateInvoiceResponse $result)
    {
        try {
            $order->addCommentToStatusHistory(__('Sent invoice to ERP. DocNo: %1. RecId: %2', $result->getDocNo(),
                $result->getRecId()), $order->getStatus());
            $this->orderResource->save($order);
        } catch (\Exception $e) {
            $this->logger->error("ERP CreateInvoice ERROR: {$e->getMessage()}");
        }
    }
}
