<?php
namespace Encomage\ErpIntegration\Model\Api;

use Zend\Http\Request as HttpRequest;
use Magento\Customer\Model\ResourceModel\Customer as CustomerResource;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Directory\Model\CountryFactory;
use Magento\Framework\Serialize\Serializer\Json as SerializerJson;

/**
 * Class Customer
 * @package Encomage\ErpIntegration\Model\Api
 */
class Customer extends Request
{
    /**
     * @var CustomerResource
     */
    private $customerResource;
    /**
     * @var SerializerJson
     */
    private $json;
    
    /**
     * @var CountryFactory
     */
    private $countryFactory;
    
    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * Customer constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param CustomerResource $customerResource
     * @param CustomerFactory $customerFactory
     * @param SerializerJson $json
     * @param CountryFactory $countryFactory
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        CustomerResource $customerResource,
        CustomerFactory $customerFactory,
        SerializerJson $json,
        CountryFactory $countryFactory
    )
    {
        parent::__construct($scopeConfig, $json);
        $this->customerResource = $customerResource;
        $this->customerFactory = $customerFactory;
        $this->countryFactory = $countryFactory;
        $this->json = $json;
    }

    /**
     * @api
     * @return mixed
     */
    public function getAllCustomers()
    {
        $this->setApiLastPoint('GetCustomerInfo');
        $this->setApiMethod(HttpRequest::METHOD_GET);
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
        $this->setApiLastPoint('GetCustomerInfo');
        $this->setApiMethod(HttpRequest::METHOD_GET);
        $this->setAdditionalDataUrl(["CustomerCode" => $customerCode]);
        $result = $this->sendApiRequest();
        $result = $this->json->unserialize($result);
        if (is_object($result)){
            $result = get_object_vars($result);
        }
        return $result;
    }

    /**
     * Method for create or update customer info in ERP system
     *
     * @api
     * @param $customerId
     * @param $phone
     * @return mixed
     */
    public function createOrUpdateCustomer($customerId, $phone = null)
    {
        /** @var \Magento\Customer\Model\Customer $customer */
        $customer = $this->customerFactory->create()->load($customerId);
        $customerCode = null;
        if ($customer->getCustomAttribute('erp_customer_code')) {
            $customerCode = $customer->getCcustomAttribute('erp_customer_code')->getValue();
        }
        $chooseMethod = ($customerCode) ? 'updatecustomer' : 'createcustomer';
        $data = $this->_prepareCustomerData($customer, $customerCode, $phone);
        $this->setApiLastPoint($chooseMethod);
        $this->setAdditionalDataContent($data);
        $this->setApiMethod(HttpRequest::METHOD_POST);
        $result = $this->sendApiRequest();
        $result = $this->json->unserialize($result);
        if (is_object($result)) {
            $result = get_object_vars($result);
        }

        if ($customerCode == null && $result['customerCode']) {
            $customerData = $customer->getDataModel();
            $customerData->setCustomAttribute('erp_customer_code', $result['customerCode']);
            $customer->updateData($customerData);
            $this->customerResource->save($customer);
        }
        return $result;
    }

    /**
     * @param $customer
     * @param string $customerCode
     * @param $phone
     * @return mixed
     */
    protected function _prepareCustomerData($customer, $customerCode, $phone)
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
        if ($phone) {
            $data[$fieldName]['customerTelephone'] = $phone;
        }
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
        return $data;
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
    public function setApiLastPoint($point = 'GetCustomerInfo')
    {
        return $this->apiLastPoint = $point;
    }

    /**
     * @param string $method
     * @return string
     */
    public function setApiMethod($method = HttpRequest::METHOD_GET)
    {
        return $this->apiMethod = $method;
    }

    /**
     * @param array $data
     * @return array
     */
    public function setAdditionalDataUrl(array $data = [])
    {
        return $this->additionalDataUrl = $data;
    }

    /**
     * @param array $content
     * @return array
     */
    public function setAdditionalDataContent(array $content = [])
    {
        return $this->additionalDataContent = $content;
    }
}