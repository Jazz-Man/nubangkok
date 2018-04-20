<?php
namespace Encomage\ErpIntegration\Model\Api;

use Zend\Http\Request as HttpRequest;

class Product extends Request
{
    /**
     * @return mixed
     */
    public function getAllProducts()
    {
        $this->_setLastPoint('GetProductList');
        $this->_setApiMethod(HttpRequest::METHOD_GET);
        $this->_setAdditionalDataUrl([
            'Branchpricedisplay' => 1,
            "CategoryDisplaySubCat" => 1
        ]);
        $result = $this->sendApiRequest();
        return $result;
    }

    /**
     * @param string $point
     * @return string
     */
    protected function _setLastPoint($point = 'GetProductList')
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