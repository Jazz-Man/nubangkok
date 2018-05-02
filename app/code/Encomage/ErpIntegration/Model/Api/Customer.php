<?php
namespace Encomage\ErpIntegration\Model\Api;

use Zend\Http\Request as HttpRequest;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Directory\Model\CountryFactory;
use Encomage\Nupoints\Model\NupointsRepository;

/**
 * Class Customer
 * @package Encomage\ErpIntegration\Model\Api
 */
class Customer extends Request
{
    /**
     * @var CustomerRepository
     */
    private $customerRepository;
    
    /**
     * @var NupointsRepository
     */
    private $nupointsRepository;
    
    /**
     * @var CountryFactory
     */
    private $countryFactory;

    /**
     * Customer constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param CustomerRepository $customerRepository
     * @param NupointsRepository $nupointsRepository
     * @param CountryFactory $countryFactory
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig, 
        CustomerRepository $customerRepository,
        NupointsRepository $nupointsRepository,
        CountryFactory $countryFactory
    )
    {
        parent::__construct($scopeConfig);
        $this->customerRepository = $customerRepository;
        $this->nupointsRepository = $nupointsRepository;
        $this->countryFactory = $countryFactory;
    }

    /**
     * @api
     * @return mixed
     */
    public function getAllCustomers()
    {
        $this->_setApiLastPoint('GetCustomerInfo');
        $this->_setApiMethod(HttpRequest::METHOD_GET);
        $result = $this->sendApiRequest();
        if (is_object($result)){
            $result = get_object_vars($result);
        }
        return $result;
    }

    /**
     * @api
     * @param string $customerCode
     * @return mixed
     */
    public function getSpecificCustomer($customerCode)
    {
        $this->_setApiLastPoint('GetCustomerInfo');
        $this->_setApiMethod(HttpRequest::METHOD_GET);
        $this->_setAdditionalDataUrl(["CustomerCode" => $customerCode]);
        $result = $this->sendApiRequest();
        if (is_object($result)){
            $result = get_object_vars($result);
        }
        return $result;
    }

    /**
     * Method for create or update customer info in ERP system
     * 
     * @api
     * @param \Magento\Customer\Model\Data\Customer $customer
     * @return mixed
     */
    public function createOrUpdateCustomer($customer)
    {
        $customerCode = null;
        if ($customer->getCustomAttribute('erp_customer_code')) {
            $customerCode = $customer->getCustomAttribute('erp_customer_code')->getValue();
        }
        $chooseMethod = ($customerCode) ? 'updatecustomer' : 'createcustomer';
        $this->_setApiLastPoint($chooseMethod);
        
        $data = $this->_prepareCustomerData($customer, $customerCode);
        $this->_setAdditionalDataContent($data);
        
        $this->_setApiMethod(HttpRequest::METHOD_POST);
        $result = $this->sendApiRequest();
        if (is_object($result)){
            $result = get_object_vars($result);
        }
        if ($customerCode == null) {
            $customer->setCustomAttribute('erp_customer_code', $result['customerCode']);
            $this->customerRepository->save($customer);
        }
        return $result;
    }

    /**
     * Get points from erp system
     * 
     * @api
     * @return array|mixed
     */
    public function getPoints()
    {
        $this->_setApiLastPoint('GetCustomerPoint');
        $this->_setApiMethod(HttpRequest::METHOD_GET);

        $result = $this->sendApiRequest();
        if (is_object($result)){
            $result = get_object_vars($result);
        }
        return $result;
    }

    /**
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @param string $customerCode
     * @return mixed
     */
    protected function _prepareCustomerData($customer, $customerCode)
    {
        $fieldName = 'Customer';
        $data[$fieldName] = [
            'CustomerCode' => $customerCode,
            'prenameCode' => $customer->getPrefix(),
            'customerName' => $customer->getFirstname() . ' ' . $customer->getLastname(),
            'customerAddressCountryCode' => $customer->getCreatedIn(),
            'customerTypeCode' => 'Silver',
            'memberCode' => '',
            'salespersonCode' => 'customer',
            'paymentTermCode' => 'cash'
        ];
        if ($addresses = $customer->getAddresses()) {
            $street = '';
            $customerAddress = [];
            foreach ($addresses as $address) {
                $customerAddress = $address;
                if ($address->getStreet()){
                    foreach ($address->getStreet() as $item) {
                        $street .= $item . ' ';
                    }
                }
                break;
            }
            $countryName = $this->_getCountryName($customerAddress->getCountryId());
            $dataAddress[$fieldName] = [
                'customerTaxid' => '',
                'customerBranchno' => '',
                'customerAddress' => trim($street),
                'customerTelephone' => $customerAddress->getTelephone(),
                'customerAddressCity' => $customerAddress->getCity(),
                'customerAddressDistrict' => '',
                'customerAddressProvince' => $countryName,
                'customerAddressPostCode' => $customerAddress->getPostcode(),
            ];
            $data = array_merge($data[$fieldName], $dataAddress[$fieldName]);
        }
        $points = ($this->_getNupoints($customer->getId())) ? $this->_getNupoints($customer->getId()) : 0;
        $data[$fieldName]['RebatePoint'] = $points;
        return $data;
    }

    /**
     * Get NuPoints from DataBase by customer_id
     * 
     * @param integer $customerId
     * @return \Encomage\Nupoints\Api\Data\NupointsInterface
     */
    protected function _getNupoints($customerId)
    {
        return $this->nupointsRepository->getByCustomerId($customerId)->getNupoints();
    }

    /**
     * @param string $countryCode
     * @return mixed
     */
    protected function _getCountryName($countryCode)
    {
        $country = $this->countryFactory->create()->loadByCode($countryCode);
        return $country->getName();
    }

    /**
     * @param string $point
     * @return string
     */
    protected function _setApiLastPoint($point = 'GetCustomerInfo')
    {
        return $this->apiPoint = $point;
    }

    /**
     * @param string $method
     * @return string
     */
    protected function _setApiMethod($method = HttpRequest::METHOD_GET)
    {
        return $this->apiMethod = $method;
    }

    /**
     * @param array $data
     * @return array
     */
    protected function _setAdditionalDataUrl(array $data = [])
    {
        return $this->additionalDataUrl = $data;
    }

    /**
     * @param array $content
     * @return array
     */
    protected function _setAdditionalDataContent(array $content = [])
    {
        return $this->additionalDataContent = $content;
    }
}