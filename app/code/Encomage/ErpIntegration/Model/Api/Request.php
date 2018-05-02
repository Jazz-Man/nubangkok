<?php
namespace Encomage\ErpIntegration\Model\Api;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class Request
 * @package Encomage\ErpIntegration\Model\Api
 */
abstract class Request
{
    const ERP_LOGIN = 'erp_etoday_settings/erp_authorization/login';
    const ERP_PASSWORD = 'erp_etoday_settings/erp_authorization/password';
    const ERP_HOST_NAME = 'erp_etoday_settings/erp_authorization/host_name';
    const ERP_COMPCODE = 'erp_etoday_settings/additional_settings/compcode';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    
    /**
     * Data for api request to eToday mast by use actual keys
     * written in URL
     *
     * @var array
     */
    protected $additionalDataUrl = [];

    /**
     * Data for api request to eToday mast by use actual keys
     * written in Content request
     *
     * @var array
     */
    protected $additionalDataContent = [];
    
    /**
     * Methods - GET, POST, PUT, DELETE.
     *
     * @var string
     */
    protected $apiMethod;
    
    /**
     * Method name from eToday
     *
     * @var string
     */
    protected $apiPoint;

    /**
     * Request constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Api request
     * 
     * @return mixed
     */
    public function sendApiRequest()
    {
        $dataUrl = [
            "userAccount" => $this->_getLogin(),
            "userPassword" => $this->_getPassword(),
            "compCode" => $this->_getCompCode(),
            "testmode" => 1
        ];
        if (!empty($this->additionalDataUrl)) {
            $dataUrl = array_merge($dataUrl, $this->additionalDataUrl);
        }

        $apiURL = $this->_getHostName() . '/' . $this->_getApiLastPoint() . $this->_getAuthorization($dataUrl);
       
        // parameters passing with URL
        $data_string = json_encode($this->_getAdditionalDataContent());
        $ch = curl_init($apiURL);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->_getApiMethod());
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Content-Length: " . strlen($data_string)));
        $response = curl_exec($ch);
        //decoding generated token and saving it in a variable
        $response = json_decode($response);
        return $response;
    }

    /**
     * Get ERP eToday Login
     *
     * @return string
     */
    protected function _getLogin()
    {
        return $this->scopeConfig->getValue(self::ERP_LOGIN);
    }

    /**
     * Get ERP eToday Password
     *
     * @return string
     */
    protected function _getPassword()
    {
        return $this->scopeConfig->getValue(self::ERP_PASSWORD);
    }

    /**
     * Get ERP eToday Host Name
     *
     * @return string
     */
    protected function _getHostName()
    {
        return $this->scopeConfig->getValue(self::ERP_HOST_NAME);
    }

    /**
     * Get ERP eToday Company Code
     *
     * @return string
     */
    protected function _getCompCode()
    {
        return $this->scopeConfig->getValue(self::ERP_COMPCODE);
    }

    /**
     * @param $data
     * @return string
     */
    protected function _getAuthorization($data)
    {
        $result = '?';
        foreach ($data as $kay =>$value){
            $result .= $kay . '=' . $value . '&';
        }
        $result = trim($result, '&');
        return $result;
    }

    /**
     * @return string
     */
    protected function _getApiLastPoint()
    {
        return $this->apiPoint;
    }

    /**
     * @return string
     */
    protected function _getApiMethod()
    {
        return $this->apiMethod;
    }

    /**
     * @return array
     */
    protected function _getAdditionalDataUrl()
    {
        return $this->additionalDataUrl;
    }

    /**
     * @return array
     */
    protected function _getAdditionalDataContent()
    {
        return $this->additionalDataContent;
    }

    abstract protected function _setApiLastPoint($point); // todo: return one of - GetProductList, createcustomer, updatecustomer, GetCustomerInfo, GetCustomerTypeList, GetCountryList, Get SalesPriceGroupList

    abstract protected function _setApiMethod($method);// todo: return one of - GET, POST, PUT, DELETE.

    abstract protected function _setAdditionalDataUrl(array $data = []);

    abstract protected function _setAdditionalDataContent(array $content = []);
}