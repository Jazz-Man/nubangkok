<?php
namespace Encomage\ErpIntegration\Model\Api;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json as SerializerJson;
/**
 * Class Request
 * @package Encomage\ErpIntegration\Model\Api
 */
abstract class Request
{
    const ERP_LOGIN = 'erp_etoday_settings/erp_authorization/login';
    const ERP_PASSWORD = 'erp_etoday_settings/erp_authorization/password';
    const ERP_HOST_NAME = 'erp_etoday_settings/erp_authorization/host_name';
    const ERP_COMPCODE = 'erp_etoday_settings/erp_authorization/compcode';
    const ERP_ENABLED_TEST_MODE = 'erp_etoday_settings/erp_authorization/enabled_test_mode';
    const ERP_WAREHOUSE_CODE = 'erp_etoday_settings/erp_authorization/warehouse_code';

    const ERP_COLOR_CODES = 'erp_etoday_settings/color_settings/color_code';
    const ERP_BAGS_CODES = 'erp_etoday_settings/category_type_bags/bags_codes';
    const ERP_SHOE_CODES = 'erp_etoday_settings/category_type_shoe/shoe_codes';
    const ERP_CATEGORY_CODES = 'erp_etoday_settings/categories/categories_codes';
    
    const ORDER_STATUS_PENDING_NOT_SENT = 'pending_not_sent';
    const ORDER_STATUS_PENDING = 'pending';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var SerializerJson
     */
    protected $serializerJson;
    
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
    protected $apiLastPoint;

    /**
     * Request constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param SerializerJson $serializerJson
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig, 
        SerializerJson $serializerJson
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->serializerJson = $serializerJson;
    }

    /**
     * @return array|bool|float|int|mixed|null|string
     * @throws \Exception
     */
    public function sendApiRequest()
    {
        $dataUrl = [
            "userAccount" => $this->_getLogin(),
            "userPassword" => $this->_getPassword(),
            "compCode" => $this->_getCompCode(),
            "warehouseCode" => $this->_getWarehouseCode()
        ];
        if ($this->_isEnabledTestMode()) {
            $dataUrl['testmode'] = 1;
        } 
        if (!empty($this->additionalDataUrl)) {
            $dataUrl = array_merge($dataUrl, $this->additionalDataUrl);
        }

        $apiURL = $this->_getHostName() . '/' . $this->_getApiLastPoint() . $this->_getAuthorization($dataUrl);

        $data_string = $this->serializerJson->serialize($this->_getAdditionalDataContent());
        $ch = curl_init($apiURL);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->_getApiMethod());
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json", "Content-Length: " . strlen($data_string)]);
        $response = $this->serializerJson->unserialize(curl_exec($ch));
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
     * @return mixed
     */
    protected function _isEnabledTestMode()
    {
        return $this->scopeConfig->getValue(self::ERP_ENABLED_TEST_MODE);
    }

    /**
     * @return mixed
     */
    protected function _getWarehouseCode()
    {
        return $this->scopeConfig->getValue(self::ERP_WAREHOUSE_CODE);
    }

    

    /**
     * Get Bags category codes and category path
     *
     * @return mixed
     */
    protected function _getBagsCodes()
    {
        return $this->scopeConfig->getValue(self::ERP_BAGS_CODES);
    }

    /**
     * Get Shoe category codes and category path
     *
     * @return mixed
     */
    protected function _getShoeCodes()
    {
        return $this->scopeConfig->getValue(self::ERP_SHOE_CODES);
    }

    /**
     * Get category codes and category path
     *
     * @return mixed
     */
    protected function _getCategoryCodes()
    {
        return $this->scopeConfig->getValue(self::ERP_CATEGORY_CODES);
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
        return $this->apiLastPoint;
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

    /**
     * @param $point
     * @return mixed
     */
    abstract public function setApiLastPoint($point);

    /**
     * @param $method
     * @return mixed
     */
    abstract public function setApiMethod($method);

    /**
     * @param array $data
     * @return mixed
     */
    abstract public function setAdditionalDataUrl(array $data = []);

    /**
     * @param array $content
     * @return mixed
     */
    abstract public function setAdditionalDataContent(array $content = []);
}