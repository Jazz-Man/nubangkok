<?php

namespace Encomage\ErpIntegration\Helper;

use Magento\Customer\Model\Address\Config as AddressConfig;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

/**
 * Class ErpApiInvoiceAddress.
 */
class ErpApiInvoiceAddress extends AbstractHelper
{
    /**
     * @var \Magento\Customer\Model\Address\Config
     */
    private $addressConfig;

    /**
     * ErpApiInvoiceAddress constructor.
     *
     * @param \Magento\Framework\App\Helper\Context  $context
     * @param \Magento\Customer\Model\Address\Config $addressConfig
     */
    public function __construct(
        Context $context,
        AddressConfig $addressConfig
    ) {
        $this->addressConfig = $addressConfig;
        parent::__construct($context);
    }

    /**
     * @param mixed  $address
     * @param string $typeCode
     *
     * @return string
     */
    public function prepareAddress($address, $typeCode = 'oneline')
    {
        /** @var \Magento\Customer\Block\Address\Renderer\DefaultRenderer $format */
        $format = $this->addressConfig->getFormatByCode($typeCode)->getRenderer();

        return $format->renderArray($address);
    }
}
