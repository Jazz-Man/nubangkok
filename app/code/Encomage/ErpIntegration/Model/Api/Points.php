<?php
namespace Encomage\ErpIntegration\Model\Api;

use Zend\Http\Request as HttpRequest;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json as SerializerJson;
use Magento\Directory\Model\CountryFactory;
use Encomage\Nupoints\Model\NupointsRepository;

/**
 * Class Customer
 * @package Encomage\ErpIntegration\Model\Api
 */
class Points extends Request
{
    private $nupointsRepository;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        NupointsRepository $nupointsRepository,
        SerializerJson $json
    )
    {
        $this->nupointsRepository = $nupointsRepository;
        parent::__construct($scopeConfig, $json);
    }

    /**
     * @return array|mixed
     */
    public function getAllNuPoints()
    {
        $this->setApiLastPoint('GetCustomerPoint');
        $this->setApiMethod(HttpRequest::METHOD_GET);

        $result = $this->sendApiRequest();
        if (is_object($result)) {
            $result = get_object_vars($result);
        }
        return $result;
    }

    /**
     * @param $customerCode
     * @return array|mixed
     */
    public function getNuPointByCustomerCode($customerCode)
    {
        $this->setApiLastPoint('GetCustomerPoint');
        $this->setApiMethod(HttpRequest::METHOD_GET);
        $this->setAdditionalDataUrl([
            "CustomerCode" => $customerCode
        ]);

        $result = $this->sendApiRequest();
        if (is_object($result)) {
            $result = get_object_vars($result);
        }
        $options['customer_code'] = $customerCode;
        $options['nupoints'] = $result['RebatePoint'];
        $result = $this->nupointsRepository->changeNupointsCount($options, 'update');
        return $result;
    }

    /**
     * @param array $content
     * @return array
     */
    public function setAdditionalDataContent(array $content = [])
    {
        return $this->additionalDataContent = $content;
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
     * @param $lastPoint
     * @return mixed
     */
    public function setApiLastPoint($lastPoint)
    {
        return $this->apiLastPoint = $lastPoint;
    }

    /**
     * @param $method
     * @return mixed
     */
    public function setApiMethod($method)
    {
        return $this->apiMethod = $method;
    }
}