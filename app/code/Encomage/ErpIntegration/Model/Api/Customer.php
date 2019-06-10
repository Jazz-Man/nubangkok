<?php
namespace Encomage\ErpIntegration\Model\Api;

use Zend\Http\Request as HttpRequest;
use Magento\Customer\Model\ResourceModel\Customer as CustomerResource;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Directory\Model\CountryFactory;
use Magento\Framework\Serialize\Serializer\Json as SerializerJson;
use Encomage\ErpIntegration\Logger\Logger;

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
     * @var Logger
     */
    private $logger;

    /**
     * Customer constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param CustomerResource $customerResource
     * @param CustomerFactory $customerFactory
     * @param SerializerJson $json
     * @param CountryFactory $countryFactory
     * @param Logger $logger
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        CustomerResource $customerResource,
        CustomerFactory $customerFactory,
        SerializerJson $json,
        CountryFactory $countryFactory,
        Logger $logger
    ) {
        parent::__construct($scopeConfig, $json);
        $this->customerResource = $customerResource;
        $this->customerFactory = $customerFactory;
        $this->countryFactory = $countryFactory;
        $this->json = $json;
        $this->logger = $logger;
    }

    /**
     * Method for create or update customer info in ERP system
     *
     * @param $customerId
     * @param null $phone
     * @return array|bool|float|int|mixed|null|string
     * @throws \Exception
     */
    public function createOrUpdateCustomer($customerId, $phone = null)
    {
        /** @var \Magento\Customer\Model\Customer $customer */
        $customer = $this->customerFactory->create()->load($customerId);
        $customerCode = $customer->getErpCustomerCode();
        $chooseMethod = ($customerCode) ? 'updatecustomer' : 'createcustomer';
        $data = $this->_prepareCustomerData($customer, $customerCode, $phone);
        if (!$data || $data == null) {
            throw new \Exception(__('Data is empty'));
        }
        $this->setApiLastPoint($chooseMethod);
        $this->setAdditionalDataContent($data);
        $this->setApiMethod(HttpRequest::METHOD_POST);
        $result = $this->sendApiRequest();
        if (empty($result) || !$result) {
            $this->logger->addInfo('The ERP system sent an empty response.');
            //throw new \Exception(__('The ERP system sent an empty response.'));
        }
        if ($customerCode == null && !empty($result['customerCode'])) {
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
            'customerName' => $customer->getFirstname() . ' ' . $customer->getLastname(),
            'customerAddressCountryCode' => $customer->getCreatedIn(),
            'customerEmail' => $customer->getEmail(),
            'customerTypeCode' => 'Silver',
            'salespersonCode' => 'admin',
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
                'customerTelephone' => $customerAddress->getTelephone(),
                'customerTaxid' => 'tax1',
                'customerBranchno' => 'Online',
                'customerAddress' => trim($street),
                'customerAddressCity' => $customerAddress->getCity(),
                'customerAddressProvince' => $countryName,
                'customerAddressPostCode' => $customerAddress->getPostcode(),
            ];
            $data[$fieldName] = array_merge($data[$fieldName], $dataAddress[$fieldName]);
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