<?php

namespace Encomage\DropdownFields\Model\Api;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\HTTP\Client\Curl;

/**
 * Class Request
 * @package Encomage\DropdownFields\Model\Api
 */
class Response
{
    /**
     * @var Json
     */
    protected $json;

    /**
     * @var Curl
     */
    protected $curl;

    /**
     * Response constructor.
     * @param Json $json
     * @param Curl $curl
     */
    public function __construct(Json $json, Curl $curl) {
        $this->json = $json;
        $this->curl =$curl;
    }

    /**
     * @param string $url
     * @return array|bool|float|int|mixed|string|null
     */
    public function getResponseFromApi(string $url)
    {
        $this->curl->get($url);
        $response = $this->curl->getBody();
        return $this->json->unserialize($response);
    }
}
