<?php
namespace Encomage\ErpIntegration\Model\Api;

use Zend\Http\Request as HttpRequest;

/**
 * Class Customer
 * @package Encomage\ErpIntegration\Model\Api
 */
class Customer extends Request
{
    /**
     * @return mixed
     */
    public function getAllCustomers()
    {
        $this->_setLastPoint('GetCustomerInfo');
        $this->_setApiMethod(HttpRequest::METHOD_GET);
        $result = $this->sendApiRequest();
        return $result;
    }

    /**
     * @param string $customerCode
     * @return mixed
     */
    public function getSpecificCustomer($customerCode = "14000001")
    {
        $this->_setLastPoint('GetCustomerInfo');
        $this->_setApiMethod(HttpRequest::METHOD_GET);
        $this->_setAdditionalDataUrl(["CustomerCode" => $customerCode]);
        $result = $this->sendApiRequest();
        return $result;
    }

    public function createCustomer($customer)
    {
        $this->_setLastPoint('createcustomer');
        $this->_setApiMethod(HttpRequest::METHOD_POST);
        $this->_setAdditionalDataContent([
            'Customer' => [
                "CustomerCode" => "cash",
                "prenameCode" => "คุณ",
                "customerName" => $customer->getName(),
                "customerTypeCode" => "Silver",
                "memberCode" => "SS100010",
                "customerAddress" => "test address",
                "customerAddressDistrict" => "District Test",
                "customerAddressCity" => "City Test",
                "customerAddressProvince" => "Country",
                "customerAddressPostCode" => "69000",
                "customerAddressCountryCode" => "UA",
                "customerTaxid" => "0105548031880",
                "customerBranchno" => "Headquarters",
                "customerTelephone" => $customer->getPhone(),
                "salespersonCode" => "customer",
                "paymentTermCode" => "cash"
            ]
        ]);
        $result = $this->sendApiRequest();
        return $result;
    }

    public function updateCustomer($customer)
    {
        $this->_setLastPoint('updatecustomer');
        $this->_setApiMethod(HttpRequest::METHOD_PUT);
        $this->_setAdditionalDataContent([
            'Customer' => [
                "CustomerCode" => "cash",
                "prenameCode" => "คุณ",
                "customerName" => $customer->getName(),
                "customerTypeCode" => "Silver",
                "memberCode" => "SS100010",
                "customerAddress" => "test address",
                "customerAddressDistrict" => "District Test",
                "customerAddressCity" => "City Test",
                "customerAddressProvince" => "Country",
                "customerAddressPostCode" => "69000",
                "customerAddressCountryCode" => "UA",
                "customerTaxid" => "0105548031880",
                "customerBranchno" => "Headquarters",
                "customerTelephone" => $customer->getPhone(),
                "salespersonCode" => "customer",
                "paymentTermCode" => "cash"
            ]
        ]);
        $result = $this->sendApiRequest();
        return $result;
    }

    public function getCustomerNuPoints($customerId)
    {
        
    }

    /**
     * @param string $point
     * @return string
     */
    protected function _setLastPoint($point = 'GetCustomerInfo')
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