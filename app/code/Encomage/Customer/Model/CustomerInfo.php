<?php

namespace Encomage\Customer\Model;

use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Customer\Model\ResourceModel\Customer;

class CustomerInfo extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     */
    private $_currentCustomer;

    /**
     * CustomerInfo constructor.
     * @param CurrentCustomer $currentCustomer
     * @param Context $context
     * @param Registry $registry
     * @param Customer $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        CurrentCustomer $currentCustomer,
        Context $context,
        Registry $registry,
        Customer $resource,
        AbstractDb $resourceCollection = null,
        array $data = [])
    {
        $this->_currentCustomer=$currentCustomer;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @return mixed|null
     */
    public function getLineId()
    {
        $lineId = $this->getCustomer()->getCustomAttribute('line_id')?
            $this->getCustomer()->getCustomAttribute('line_id')->getValue(): null;
        return $lineId;
    }

    /**
     * @return null|string
     */
    public function getGender()
    {
        $genderCodeId = $this->getCustomer()->getGender();
        if ($genderCodeId) {
            switch ((int)$genderCodeId) {
                case 1:
                    return "Male";
                case 2:
                    return "Female";
                case 3:
                    return "Not Specified";
            }
        }
        return null;
    }

    /**
     * @return \Magento\Customer\Api\Data\CustomerInterface|null
     */
    protected function getCustomer()
    {
        try {
            return $this->_currentCustomer->getCustomer();
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

}