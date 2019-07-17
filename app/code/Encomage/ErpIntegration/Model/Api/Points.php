<?php
namespace Encomage\ErpIntegration\Model\Api;

use Exception;
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
    /**
     * @var NupointsRepository
     */
    private $nupointsRepository;
    /**
     * @var SerializerJson
     */
    private $json;

    /**
     * Points constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param NupointsRepository $nupointsRepository
     * @param SerializerJson $json
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        NupointsRepository $nupointsRepository,
        SerializerJson $json
    )
    {
        parent::__construct($scopeConfig, $json);
        $this->nupointsRepository = $nupointsRepository;
        $this->json = $json;
    }

    /**
     * @param $customerCode
     * @return array|bool|\Encomage\Nupoints\Api\Data\NupointsInterface|float|int|mixed|null|string
     * @throws \Exception
     */
    public function getNuPointByCustomerCode($customerCode)
    {
        $this->setApiLastPoint('GetCustomerPoint');
        $this->setApiMethod(HttpRequest::METHOD_GET);
        $this->setAdditionalDataUrl([
            'CustomerCode' => $customerCode
        ]);
        $result = $this->sendApiRequest();
        if (empty($result) || !$result) {
            throw new Exception(__('The ERP system sent an empty response.'));
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