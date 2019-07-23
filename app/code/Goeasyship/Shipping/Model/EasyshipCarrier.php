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

use Goeasyship\Shipping\Model\Api\Request;
use Magento\Directory\Model\CountryFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\ResourceModel\Order\Shipment\Track\CollectionFactory;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Tracking\Result;
use Magento\Shipping\Model\Tracking\Result\StatusFactory;
use Magento\Shipping\Model\Tracking\ResultFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class EasyshipCarrier
 *
 * @package Goeasyship\Shipping\Model
 */
class EasyshipCarrier extends AbstractCarrier implements CarrierInterface
{

    /**
     * @var string
     */
    protected $_code = 'easyship';
    /**
     * @var \Magento\Quote\Model\Quote\Address\RateRequest
     */
    protected $_request;
    /**
     * @var DataObject
     */
    protected $_rawRequest;
    /**
     * @var \Goeasyship\Shipping\Model\Api\Request
     */
    protected $_easyshipApi;
    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    protected $_countryFactory;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var
     */
    protected $storeId;
    /**
     * @var \Magento\Shipping\Model\Rate\ResultFactory
     */

    protected $_rateResultFactory;
    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
     */
    protected $_rateMethodFactory;
    /**
     * @var \Magento\Shipping\Model\Tracking\Result\StatusFactory
     */
    protected $_statusFactory;
    /**
     * @var \Magento\Shipping\Model\Tracking\ResultFactory
     */
    protected $_trackFactory;
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Shipment\Track\CollectionFactory
     */
    protected $_trackCollectionFactory;

    /**
     * EasyshipCarrier constructor.
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface                        $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory                $rateErrorFactory
     * @param \Magento\Shipping\Model\Rate\ResultFactory                                $rateResultFactory
     * @param \Magento\Shipping\Model\Tracking\ResultFactory                            $trackingResultFactory
     * @param \Magento\Shipping\Model\Tracking\Result\StatusFactory                     $statusFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory               $rateMethodFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Shipment\Track\CollectionFactory $trackCollectionFactory
     * @param \Psr\Log\LoggerInterface                                                  $logger
     * @param \Goeasyship\Shipping\Model\Api\Request                                    $easyshipApi
     * @param \Magento\Directory\Model\CountryFactory                                   $countryFactory
     * @param \Magento\Store\Model\StoreManagerInterface                                $storeManager
     * @param array                                                                     $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        ResultFactory $trackingResultFactory,
        StatusFactory $statusFactory,
        MethodFactory $rateMethodFactory,
        CollectionFactory $trackCollectionFactory,
        LoggerInterface $logger,
        Request $easyshipApi,
        CountryFactory $countryFactory,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->_easyshipApi = $easyshipApi;
        $this->_countryFactory = $countryFactory;
        $this->_storeManager = $storeManager;
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->_statusFactory = $statusFactory;
        $this->_trackFactory = $trackingResultFactory;
        $this->_trackCollectionFactory = $trackCollectionFactory;

        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * @return array|null
     */
    public function getAllowedMethods()
    {
        return null;
    }

    /**
     * @return bool
     */
    public function isTrackingAvailable()
    {
        return true;
    }

    /**
     * Get tracking information
     * @param $tracking
     * @return bool
     */
    public function getTrackingInfo($tracking)
    {
        $result = $this->getTracking($tracking);

        if ($result instanceof Result) {
            $trackings = $result->getAllTrackings();
            if ($trackings) {
                return $trackings[0];
            }
        } elseif (is_string($result) && !empty($result)) {
            return $result;
        }

        return false;
    }

    /**
     * Get tracking
     * @param $trackings
     * @return mixed
     */
    public function getTracking($trackings)
    {
        if (!is_array($trackings)) {
            $trackings = [$trackings];
        }

        $result = $this->_trackFactory->create();
        $trackings_data = $this->findByNumber($trackings);
        foreach ($trackings as $tracking) {
            $status = $this->_statusFactory->create();
            $status->setCarrier($this->_code);
            $status->setCarrierTitle($this->getConfigData('title'));
            $status->setTracking($tracking);
            $status->setPopup(1);
            if (isset($trackings_data[$tracking]['tracking_page_url'])) {
                $status->setTrackingPageUrl($trackings_data[$tracking]['tracking_page_url']);
            }
            $result->append($status);
        }

        return $result;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     *
     * @return bool|\Magento\Framework\DataObject|\Magento\Shipping\Model\Rate\Result|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Zend_Http_Client_Exception
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        $this->_createEasyShipRequest($request);

        return $this->_getQuotes();
    }

    /**
     * Find tracking data by number
     * @param array $trackings
     * @return array
     */
    protected function findByNumber(array $trackings)
    {
        $elements = $this->_trackCollectionFactory->create()->addFieldToFilter('track_number', ['in' => $trackings]);
        $result = [];
        foreach ($elements as $item) {
            $result[$item['track_number']] = $item;
        }
        return $result;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _createEasyShipRequest(RateRequest $request)
    {
        $this->_request = $request;

        $currencyCode = $this->_storeManager->getStore()->getCurrentCurrencyCode();

        $r = new DataObject();

        if ($request->getOrigCountry()) {
            $origCountry = $request->getOrigCountry();
        } else {
            $origCountry = $this->_scopeConfig->getValue(
                Shipment::XML_PATH_STORE_COUNTRY_ID,
                ScopeInterface::SCOPE_STORE,
                $request->getStoreId()
            );
        }

        $r->setData('origin_country_alpha2', $this->_countryFactory->create()->load($origCountry)->getData('iso2_code'));

        if ($request->getOrigPostcode()) {
            $r->setOriginPostalCode($request->getOrigPostcode());
        } else {
            $r->setOriginPostalCode($this->_scopeConfig->getValue(
                Shipment::XML_PATH_STORE_ZIP,
                ScopeInterface::SCOPE_STORE,
                $request->getStoreId()
            ));
        }

        if ($request->getDestCountryId()) {
            $destCountry = $request->getDestCountryId();
        } else {
            $destCountry = 'US';
        }

        $r->setData('destination_country_alpha2', $this->_countryFactory->create()->load($destCountry)->getData('iso2_code'));

        if ($request->getDestPostcode()) {
            $r->setDestinationPostalCode($request->getDestPostcode());
        }

        $items = [];
        if ($request->getAllItems()) {
            foreach ($request->getAllItems() as $item) {
                if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                    continue;
                }

                $itemQty = (int)$item->getQty();
                for ($i = 0; $i < $itemQty; $i++) {
                    $items[] = [
                        'actual_weight' => $this->getWeight($item->getProduct()),
                        'height' => $this->getEasyshipHeight($item->getProduct()),
                        'width' => $this->getEasyshipWidth($item->getProduct()),
                        'length' => $this->getEasyshipLength($item->getProduct()),
                        'category' => $this->getEasyshipCategory($item->getProduct()),
                        'declared_currency' => $currencyCode,
                        'declared_customs_value' => (float)$item->getPrice(),
                        'sku' => $item->getSku()
                    ];
                }
            }
        }

        $r->setItems($items);

        $this->_rawRequest = $r;
    }

    /**
     * Get weight
     *
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getStoreId()
    {
        if (empty($this->storeId)) {
            $this->storeId = $this->_storeManager->getStore()->getId();
        }

        return $this->storeId;
    }

    /**
     * Get weight
     * @param $item
     * @return int
     */
    protected function getWeight($item)
    {
        if ($item->hasWeight() && !empty($item->getWeight())) {
            return (int)$item->getWeight();
        }

        return 1;
    }

    /**
     * Get easyship category
     *
     * @param $item
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getEasyshipCategory($item)
    {
        if ($item->hasEasyshipCategory() && !empty($item->getEasyshipCategory())) {
            return $item->getEasyshipCategory();
        }

        $base_category = $this->_scopeConfig->getValue(
            'carriers/easyship/base_category',
            ScopeInterface::SCOPE_WEBSITE,
            $this->getStoreId()
        );

        if (empty($base_category)) {
            return '';
        }

        return $base_category;
    }

    /**
     * Get easyship height
     *
     * @param $item
     *
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getEasyshipHeight($item)
    {
        if ($item->hasEasyshipHeight() && !empty($item->getEasyshipHeight())) {
            return (int)$item->getEasyshipHeight();
        }

        $base_height = $this->_scopeConfig->getValue(
            'carriers/easyship/base_height',
            ScopeInterface::SCOPE_WEBSITE,
            $this->getStoreId()
        );

        if (empty($base_height)) {
            return 0;
        }

        return (int)$base_height;
    }

    /**
     * Get easyship width
     * @param $item
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
*/
    protected function getEasyshipWidth($item)
    {
        if ($item->hasEasyshipWidth() && !empty($item->getEasyshipWidth())) {
            return (int)$item->getEasyshipWidth();
        }

        $base_width = $this->_scopeConfig->getValue(
            'carriers/easyship/base_width',
            ScopeInterface::SCOPE_WEBSITE,
            $this->getStoreId()
        );

        if (empty($base_width)) {
            return 0;
        }

        return (int)$base_width;
    }

    /**
     * Get easyship length
     * @param $item
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
*/
    protected function getEasyshipLength($item)
    {
        if ($item->hasEasyshipLength() && !empty($item->getEasyshipLength())) {
            return (int)$item->getEasyshipLength();
        }

        $base_length = $this->_scopeConfig->getValue(
            'carriers/easyship/base_length',
            ScopeInterface::SCOPE_WEBSITE,
            $this->getStoreId()
        );

        if (empty($base_length)) {
            return 0;
        }

        return (int)$base_length;
    }

    /**
     * @return bool|\Magento\Shipping\Model\Rate\Result
     * @throws \Zend_Http_Client_Exception
*/
    protected function _getQuotes()
    {
        $rates = $this->_easyshipApi->getQuotes($this->_rawRequest);
        if (empty($rates) || empty($rates['rates'])) {
            return false;
        }

        $prefer_rates = $rates['rates'];

        /**
         * @var \Magento\Shipping\Model\Rate\Result $result
         * @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method
         */
        $result = $this->_rateResultFactory->create();
        foreach ($prefer_rates as $rate) {
            $method = $this->_rateMethodFactory->create();
            $method->setCarrier($this->_code);
            $method->setCarrierTitle($rate['courier_name']);
            $method->setMethod($rate['short_courier_id']);
            $method->setMethodTitle($rate['full_description']);
            $method->setCost($rate['total_charge']);
            $method->setPrice($rate['total_charge']);
            $result->append($method);
        }

        return $result;
    }
}
