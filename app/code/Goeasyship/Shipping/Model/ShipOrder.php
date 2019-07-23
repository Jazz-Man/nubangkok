<?php
/**
 * Easyship.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Easyship.com license that is
 * available through the world-wide-web at this URL:
 * https://www.apache.org/licenses/LICENSE-2.0
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Goeasyship
 * @package     Goeasyship_Shipping
 * @copyright   Copyright (c) 2018 Easyship (https://www.easyship.com/)
 * @license     https://www.apache.org/licenses/LICENSE-2.0
 */

namespace Goeasyship\Shipping\Model;

use Exception;
use Goeasyship\Shipping\Api\ShipOrderInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Config as OrderConfig;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\Order\Shipment\Track;
use Magento\Sales\Model\Order\ShipmentFactory;
use Magento\Sales\Model\Order\ShipmentRepository;
use Magento\Sales\Model\OrderRepository;
use Magento\Shipping\Model\ShipmentNotifier;

/**
 * Class ShipOrder
 *
 * @package Goeasyship\Shipping\Model
 */
class ShipOrder implements ShipOrderInterface
{
    const ORDER_IN_PROGRESS = 'processing';

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resourceConnection;
    /**
     * @var \Magento\Sales\Model\Order\Config
     */
    protected $_config;
    /**
     * @var \Magento\Sales\Model\Convert\Order
     */

    protected $_convertOrder;
    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $_order;
    /**
     * @var \Magento\Sales\Model\Order\ShipmentFactory
     */
    protected $_shipmentFactory;
    /**
     * @var \Magento\Sales\Model\OrderRepository
     */
    protected $_orderRepository;
    /**
     * @var \Magento\Sales\Model\Order\ShipmentRepository
     */
    protected $_shipmentRepository;
    /**
     * @var \Magento\Shipping\Model\ShipmentNotifier
     */
    protected $_shipmentNotifier;
    /**
     * @var \Magento\Sales\Model\Order\Shipment\Track
     */
    protected $_track;

    /**
     * ShipOrder constructor.
     *
     * @param \Magento\Framework\App\ResourceConnection     $resourceConnection
     * @param \Magento\Sales\Model\Order\Config             $config
     * @param \Magento\Sales\Model\Convert\Order            $convertOrder
     * @param \Magento\Sales\Model\OrderRepository          $orderRepository
     * @param \Magento\Sales\Model\Order\ShipmentRepository $shipmentRepository
     * @param \Magento\Shipping\Model\ShipmentNotifier      $shipmentNotifier
     * @param \Magento\Sales\Model\Order                    $order
     * @param \Magento\Sales\Model\Order\ShipmentFactory    $shipmentFactory
     * @param \Magento\Sales\Model\Order\Shipment\Track     $track
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        OrderConfig $config,
        \Magento\Sales\Model\Convert\Order $convertOrder,
        OrderRepository $orderRepository,
        ShipmentRepository $shipmentRepository,
        ShipmentNotifier $shipmentNotifier,
        Order $order,
        ShipmentFactory $shipmentFactory,
        Track $track
    ) {
        $this->_resourceConnection = $resourceConnection;
        $this->_config = $config;

        $this->_convertOrder = $convertOrder;
        $this->_order = $order;
        $this->_shipmentFactory = $shipmentFactory;
        $this->_shipmentRepository = $shipmentRepository;
        $this->_shipmentNotifier = $shipmentNotifier;
        $this->_orderRepository = $orderRepository;
        $this->_track = $track;
    }

    /**
     * @param int    $orderId
     * @param array  $items
     * @param array  $trackData
     * @param string $comment
     *
     * @return bool|int|string
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(
        $orderId,
        $items = [],
        $trackData = [],
        $comment = ''
    ) {
        $order = $this->_orderRepository->get($orderId);

        if (!$order->canShip()) {
            return false;
        }
        $shipment = $this->_convertOrder->toShipment($order);
        $countItems = count($items);
        foreach ($order->getAllItems() as $orderItem) {
            $this->_addToShip($shipment, $orderItem, $items, $countItems);
        }

        $shipment->getOrder()->setIsInProcess(true);
        $trackData = $this->validateTrackData($trackData);

        if (!empty($trackData)) {
            $this->_track->addData($trackData);
            $shipment->addTrack($this->_track);
        }

        if (!empty($comment)) {
            $shipment->addComment($comment);
        }

        $shipment->setShipmentStatus(Shipment::STATUS_NEW);

        $shipment->register();

        $connection = $this->_resourceConnection->getConnection('sales');
        $connection->beginTransaction();
        try {
            $order->setState(self::ORDER_IN_PROGRESS);
            $order->setStatus($this->_config->getStateDefaultStatus($order->getState()));
            $this->_shipmentRepository->save($shipment);
            $this->_orderRepository->save($order);
            $connection->commit();
        } catch (Exception $e) {
            $connection->rollBack();
            throw new CouldNotSaveException(
                __('Could not save a shipment, see error log for details')
            );
        }

        return $shipment->toJson();
    }

    /**
     * Add item to shipment
     *
     * @param $shipment
     * @param $orderItem
     * @param $items
     * @param $countItems
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _addToShip($shipment, $orderItem, $items, $countItems)
    {
        $needToShip = true;
        //Check need to ship
        if (is_array($items) && $countItems) {
            foreach ($items as $item) {
                if (isset($item['item_id']) && ($item['item_id'] == $orderItem->getId())) {
                    $needToShip = true;
                    $countToShip = $item['qty'];
                    break;
                }

                $needToShip = false;
            }
        }

        if (!$needToShip) {
            return false;
        }

        // Check if order item is virtual or has quantity to ship
        if (!$orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
            return false;
        }

        $qtyShipped = $countToShip ?? $orderItem->getQtyToShip();

        // Create shipment item with qty
        $shipmentItem = $this->_convertOrder->itemToShipmentItem($orderItem)->setQty($qtyShipped);

        // Add shipment item to shipment
        $shipment->addItem($shipmentItem);
    }

    /**
     * @param $trackData
     *
     * @return mixed
     */
    protected function validateTrackData($trackData)
    {
        if (isset($trackData['tracking_number'])) {
            $trackData['number'] = $trackData['tracking_number'];
            unset($trackData['tracking_number']);
        }

        return $trackData;
    }
}
