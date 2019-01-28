<?php

namespace Encomage\DropdownFields\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Encomage\DropdownFields\Model\Api\Request;
use Encomage\DropdownFields\Helper\Data;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * Class Index
 * @package Encomage\DropdownFields\Controller\Index
 */
class Index extends Action
{
    const REGION = 'region';
    const COUNTRY_CODE = 'country_code';
    const REGION_LABEL = 'region_label';
    const REGION_ENDPOINT = '/all/?key=';
    const CITY_ENDPOINT = '/search/?region=';

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var Request
     */
    protected $apiRequest;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * Index constructor.
     * @param Context $context
     * @param Request $apiRequest
     * @param Data $helper
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        Request $apiRequest,
        Data $helper,
        JsonFactory $resultJsonFactory
    )
    {
        $this->apiRequest = $apiRequest;
        $this->helper = $helper;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        if ($this->getRequest()->isAjax()) {
            $params = $this->getRequest()->getParams();
            if ($params['type'] === self::REGION ) {
                $url = $this->helper->getApiRegionDomainName() . $params[self::COUNTRY_CODE] . self::REGION_ENDPOINT . $this->helper->getApiKeyValue();
            } else {
                $url = $this->helper->getApiCityDomainName() . $params[self::COUNTRY_CODE] . self::CITY_ENDPOINT .rawurlencode($params[self::REGION_LABEL] ) . '&key=' . $this->helper->getApiKeyValue();
            }
            $response = $this->apiRequest->createRequest($url);

            return $result->setData($response);
        }
    }
}