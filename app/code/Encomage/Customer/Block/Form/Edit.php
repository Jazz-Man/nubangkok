<?php


namespace Encomage\Customer\Block\Form;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Encomage\Customer\Block\Address\Edit as AddressEdit;

class Edit extends \Magento\Customer\Block\Form\Edit
{

    protected $addressRepository;

    protected $billingAddress = null;

    protected $addressEditBlock;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        CustomerRepositoryInterface $customerRepository,
        AccountManagementInterface $customerAccountManagement,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        AddressEdit $addressEditBlock,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $customerSession,
            $subscriberFactory,
            $customerRepository,
            $customerAccountManagement,
            $data
        );

        $this->addressRepository = $addressRepository;
        $this->addressEditBlock = $addressEditBlock;
    }

    public function getAddress()
    {
        if ($this->billingAddress === null) {
            $addressId = $this->getCustomer()->getDefaultBilling();
            if(!$addressId){
                $addressId = $this->getCustomer()->getDefaultShipping();
            }
            $this->billingAddress = ($addressId)
                ? $this->addressRepository->getById($addressId) : false;
        }

        return $this->billingAddress;
    }

    public function getCountryHtmlSelect($defValue = null, $name = 'country_id', $id = 'country', $title = 'Country')
    {
        $this->addressEditBlock->setAddress($this->getAddress());
        return $this->addressEditBlock->getCountryHtmlSelect($defValue, $name, 'country-billing', $title);
    }

}