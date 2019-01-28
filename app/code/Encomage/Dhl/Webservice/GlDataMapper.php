<?php

namespace Encomage\Dhl\Webservice;

use Dhl\Shipping\Webservice\GlDataMapper as ParentGlDataMapperClass;
use Dhl\Shipping\Webservice\RequestType\CreateShipment\ShipmentOrder\PackageInterface;
use Dhl\Shipping\Webservice\RequestType\CreateShipment\ShipmentOrder\Service\AbstractServiceFactory;
use Dhl\Shipping\Webservice\RequestType\CreateShipment\ShipmentOrder\Service\ServiceCollectionInterface;
use Dhl\Shipping\Webservice\RequestType\CreateShipment\ShipmentOrder\ShipmentDetails\ShipmentDetailsInterface;
use Dhl\Shipping\Gla\Request\Type\PackageDetailsRequestType;
use Dhl\Shipping\Webservice\RequestType;
use Dhl\Shipping\Webservice\RequestType\CreateShipment\ShipmentOrder\Contact;
use Dhl\Shipping\Gla\Request\Type\ConsigneeAddressRequestType;
use Dhl\Shipping\Gla\Request\Type\CustomsDetailsRequestType;
use Dhl\Shipping\Gla\Request\Type\PackageRequestType;
use Dhl\Shipping\Gla\Request\Type\ReturnAddressRequestType;
use Dhl\Shipping\Gla\Request\Type\ShipmentRequestType;
use Dhl\Shipping\Webservice\RequestType\CreateShipment\ShipmentOrderInterface;
use Zend_Measure_Weight as Weight;
use Zend_Measure_Length as Length;

/**
 * Class GlDataMapper
 * @package Encomage\Dhl\Webservice
 */
class GlDataMapper extends ParentGlDataMapperClass
{

    const TIME = 946684800;

    /**
     * @var array
     */
    private $weightUomMap = [
        Weight::GRAM => 'G',
        Weight::KILOGRAM => 'KG',
        Weight::OUNCE => 'OZ',
        Weight::POUND => 'LB',
    ];

    /**
     * @var array
     */
    private $dimensionUomMap = [
        Length::INCH => 'IN',
        Length::CENTIMETER => 'CM',
        Length::MILLIMETER => 'MM',
        Length::FEET => 'FT',
        Length::METER => 'M',
        Length::YARD => 'Y',
    ];

    /**
     * @param ShipmentOrderInterface $shipmentOrder
     * @return ShipmentRequestType
     */
    public function mapShipmentOrder(ShipmentOrderInterface $shipmentOrder)
    {
        $packageTypes = [];

        $receiverType = $this->getReceiver($shipmentOrder->getReceiver());
        $returnReceiverType = $this->getReturnReceiver($shipmentOrder->getReturnReceiver());

        foreach ($shipmentOrder->getPackages() as $package) {
            $customsDetailsType = $this->getExportDocument($package);
            $packageDetailsType = $this->getPackageDetails(
                $shipmentOrder->getShipmentDetails(),
                $shipmentOrder->getServices(),
                $package,
                $shipmentOrder->getSequenceNumber()
            );
            $packageType = new PackageRequestType(
                $receiverType,
                $packageDetailsType,
                $returnReceiverType,
                $customsDetailsType
            );
            $packageTypes[] = $packageType;
        }

        $shipmentType = new ShipmentRequestType(
            $shipmentOrder->getShipmentDetails()->getPickupAccountNumber(),
            $shipmentOrder->getShipmentDetails()->getDistributionCenter(),
            $packageTypes,
            $shipmentOrder->getShipmentDetails()->getConsignmentNumber()
        );

        return $shipmentType;
    }

    /**
     * @param ShipmentDetailsInterface $shipmentDetails
     * @param ServiceCollectionInterface $services
     * @param PackageInterface $package
     * @param string $sequenceNumber
     * @return \Dhl\Shipping\Gla\Request\Type\PackageDetailsRequestType|PackageDetailsRequestType
     */
    private function getPackageDetails(
        ShipmentDetailsInterface $shipmentDetails,
        ServiceCollectionInterface $services,
        PackageInterface $package,
        $sequenceNumber
    )
    {
        $currencyCode = $package->getDeclaredValue()->getCurrencyCode();
        $weightUom = $package->getWeight()->getUnitOfMeasurement();
        if (isset($this->weightUomMap[$weightUom])) {
            $weightUom = $this->weightUomMap[$weightUom];
        }
        $dimensionUom = $package->getDimensions()->getUnitOfMeasurement();
        if (isset($this->dimensionUomMap[$dimensionUom])) {
            $dimensionUom = $this->dimensionUomMap[$dimensionUom];
        }

        /** @var \Dhl\Shipping\Webservice\RequestType\CreateShipment\ShipmentOrder\Service\Cod $codService */
        $codService = $services->getService(AbstractServiceFactory::SERVICE_CODE_COD);
        if ($codService) {
            $codAmount = $codService->getCodAmount()->getValue($currencyCode);
        } else {
            $codAmount = null;
        }

        $packageDetailsType = new PackageDetailsRequestType(
            $package->getDeclaredValue()->getCurrencyCode(),
            $shipmentDetails->getProduct(),
            $this->getPackageId($shipmentDetails, $sequenceNumber),
            $package->getWeight()->getValue($package->getWeight()->getUnitOfMeasurement()),
            $weightUom,
            null,
            null,
            $codAmount,
            $package->getDeclaredValue()->getValue($currencyCode),
            null,
            $package->getDangerousGoodsCategory(),
            $dimensionUom,
            $package->getDimensions()->getHeight($package->getDimensions()->getUnitOfMeasurement()),
            $package->getDimensions()->getLength($package->getDimensions()->getUnitOfMeasurement()),
            $package->getDimensions()->getWidth($package->getDimensions()->getUnitOfMeasurement()),
            $package->getTermsOfTrade(),
            null,
            null,
            null,
            $sequenceNumber,
            null,
            null
        );

        return $packageDetailsType;
    }

    /**
     * @param ShipmentDetailsInterface $shipmentDetails
     * @param string $sequenceNumber
     * @return string|string[]|null
     */
    private function getPackageId(ShipmentDetailsInterface $shipmentDetails, $sequenceNumber)
    {
        $time = time() - self::TIME;
        $uniquePackageId = $shipmentDetails->getCustomerPrefix() . $sequenceNumber . $time;
        $uniquePackageId = preg_replace('/[^a-zA-Z\d]/', '', $uniquePackageId);

        return $uniquePackageId;
    }

    /**
     * @param Contact\ReceiverInterface $receiver
     * @return ConsigneeAddressRequestType
     */
    private function getReceiver(Contact\ReceiverInterface $receiver)
    {
        $street = $receiver->getAddress()->getStreet();

        $receiverType = new ConsigneeAddressRequestType(
            $street[0],
            $receiver->getAddress()->getCity(),
            $receiver->getAddress()->getCountryCode(),
            $receiver->getPhone(),
            $street[1],
            null,
            $receiver->getCompanyName(),
            $receiver->getEmail(),
            $receiver->getId()->getNumber(),
            $receiver->getId()->getType(),
            $receiver->getName(),
            $receiver->getAddress()->getPostalCode(),
            $receiver->getAddress()->getState()
        );

        return $receiverType;
    }

    /**
     * @param Contact\ReturnReceiverInterface $returnReceiver
     * @return ReturnAddressRequestType
     */
    private function getReturnReceiver(Contact\ReturnReceiverInterface $returnReceiver)
    {
        $street = $returnReceiver->getAddress()->getStreet();

        $returnReceiverType = new ReturnAddressRequestType(
            $street[0],
            $returnReceiver->getAddress()->getCity(),
            $returnReceiver->getAddress()->getCountryCode(),
            $returnReceiver->getAddress()->getState(),
            $street[1],
            null,
            $returnReceiver->getCompanyName(),
            $returnReceiver->getName(),
            $returnReceiver->getAddress()->getPostalCode()
        );

        return $returnReceiverType;
    }

    /**
     * @param PackageInterface $package
     * @return array|CustomsDetailsRequestType[]
     */
    private function getExportDocument(PackageInterface $package)
    {
        $customsDetailsTypes = [];
        $currencyCode = $package->getDeclaredValue()
            ->getCurrencyCode();
        /** @var RequestType\CreateShipment\ShipmentOrder\Package\PackageItemInterface $packageItem */
        foreach ($package->getItems() as $packageItem) {
            if ($packageItem->getCustomsItemDescription()) {
                $itemDetails = new CustomsDetailsRequestType(
                    $packageItem->getCustomsItemDescription(),
                    $packageItem->getCustomsItemDescription(),
                    $packageItem->getCustomsItemDescription(),
                    $packageItem->getItemOriginCountry(),
                    $packageItem->getTariffNumber(),
                    (int)$packageItem->getQty(),
                    $packageItem->getCustomsValue()->getValue($currencyCode),
                    $packageItem->getSku()
                );
                $customsDetailsTypes[] = $itemDetails;
            }
        }

        return $customsDetailsTypes;
    }
}