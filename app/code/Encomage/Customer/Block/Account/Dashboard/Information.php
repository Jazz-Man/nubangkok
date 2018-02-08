<?php

namespace Encomage\Customer\Block\Account\Dashboard;

class Information extends \Magento\Customer\Block\Account\Dashboard\Info
{
    /**
     * Information constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
     * @param \Magento\Customer\Helper\View $helperView
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        \Magento\Customer\Helper\View $helperView,
        array $data = [])
    {
        parent::__construct($context,
            $currentCustomer,
            $subscriberFactory,
            $helperView,
            $data);
    }

    /**
     * @return string
     */
    public function getLastname()
    {
        return $this->getCustomer()->getLastname();
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->getCustomer()->getEmail();
    }

    /**
     * @return null|string
     */
    public function getDob()
    {
        return $this->getCustomer()->getDob();
    }

    /**
     * @return array|mixed
     */
    public function getLineId()
    {
        $lineId = [];
        $attr = $this->getCustomer()->getCustomAttributes();
        foreach ($attr as $item) {
            $lineId = $item->getValue();
        };
        return $lineId;
    }

}