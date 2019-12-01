<?php


namespace Encomage\Customer\Block\Form;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Encomage\Customer\Block\Address\Edit as AddressEdit;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Template\Context;
use Magento\Newsletter\Model\SubscriberFactory;

class Edit extends \Magento\Customer\Block\Form\Edit
{

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    protected $addressRepository;


    protected $billingAddress;

    protected $addressEditBlock;

    /**
     * Edit constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context  $context
     * @param \Magento\Customer\Model\Session                   $customerSession
     * @param \Magento\Newsletter\Model\SubscriberFactory       $subscriberFactory
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Customer\Api\AccountManagementInterface  $customerAccountManagement
     * @param \Magento\Customer\Api\AddressRepositoryInterface  $addressRepository
     * @param \Encomage\Customer\Block\Address\Edit             $addressEditBlock
     * @param array                                             $data
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        SubscriberFactory $subscriberFactory,
        CustomerRepositoryInterface $customerRepository,
        AccountManagementInterface $customerAccountManagement,
        AddressRepositoryInterface $addressRepository,
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
            $this->billingAddress = $addressId
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