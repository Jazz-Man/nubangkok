<?php

namespace Encomage\DropdownFields\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Encomage\DropdownFields\Model\Api\Request;
use Encomage\DropdownFields\Helper\Data;
use Magento\Framework\Controller\Result\JsonFactory;
use Encomage\DropdownFields\Model\ResourceModel\Country;

/**
 * Class Index
 * @package Encomage\DropdownFields\Controller\Index
 */
class Index extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var Request
     */
    protected $apiRequest;

    /**
     * @var Country
     */
    protected $countryResourceModel;

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
     * @param Country $countryResourceModel
     */
    public function __construct(
        Context $context,
        Request $apiRequest,
        Data $helper,
        JsonFactory $resultJsonFactory,
        Country $countryResourceModel
    )
    {
        $this->apiRequest = $apiRequest;
        $this->helper = $helper;
        $this->countryResourceModel = $countryResourceModel;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        if ($this->getRequest()->isAjax()) {
            $params = $this->getRequest()->getParams();
            $region = '';
            if (isset($params['country_code'])) {
                $region = array_unique($this->countryResourceModel->getRegionBycCountryCode($params['country_code']));
            }
            $cities = '';
            if (isset($params['region_label'])) {
                $cities = array_unique($this->countryResourceModel->getCityByRegion($params['region_label'], $params['country_code']));
            }

            $data = !empty($cities) ? $cities : $region;
            return $result->setData($data);
        }
    }
}