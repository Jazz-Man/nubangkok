<?php

namespace Encomage\Checkout\Block\Onepage;

use Magento\Sales\Model\Order\Config;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Directory\Model\CountryFactory;

/**
 * Class Success
 *
 * @package Encomage\Checkout\Block\Onepage
 */
class Success extends \Magento\Checkout\Block\Onepage\Success
{
    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    private $_countryFactory;

    /**
     * Success constructor.
     * @param Context $context
     * @param CheckoutSession $checkoutSession
     * @param CountryFactory $countryFactory
     * @param Config $orderConfig
     * @param HttpContext $httpContext
     * @param array $data
     */
    public function __construct(
        Context $context,
        CheckoutSession $checkoutSession,
        CountryFactory $countryFactory,
        Config $orderConfig,
        HttpContext $httpContext,
        array $data = [])
    {
        $this->_countryFactory = $countryFactory;
        parent::__construct(
            $context,
            $checkoutSession,
            $orderConfig,
            $httpContext,
            $data);
    }


    /**
     * @return array
     */
    public function getShippingAddress()
    {
        $addresses = $this->_checkoutSession->getLastRealOrder()->getAddresses();
        $shippingAddress = [];
        foreach ($addresses as $item) {
            if ($item->getAddressType() == 'shipping') {
                $shippingAddress = $item->getData();
            }
        }
        return $shippingAddress;
    }

    /**
     * @return string
     */
    public function getCountryNameByCode()
    {
        $countryCode=$this->getShippingAddress();
        if (isset($countryCode['country_id'])) {
            $country = $this->_countryFactory->create()->loadByCode($countryCode['country_id']);
            return $country->getName();
        }
        return '';
    }

}
