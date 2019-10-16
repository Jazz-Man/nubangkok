<?php

namespace Encomage\Customer\Plugin\CustomerData;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\UrlInterface;
use Magento\Customer\Model\Session as CustomerSession;

/**
 * Class Customer
 *
 * @package Encomage\Customer\Plugin\CustomerData
 */
class Customer
{

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $json;
    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;
    /**
     * @var array|mixed
     */
    private $loggedCustomerUrls;
    /**
     * @var array|mixed
     */
    private $notLoggedCustomerUrls;

    /**
     * Customer constructor.
     *
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     * @param \Magento\Framework\UrlInterface              $urlBuilder
     * @param \Magento\Customer\Model\Session              $customerSession
     * @param array                                        $data
     */
    public function __construct(
        Json $json,
        UrlInterface $urlBuilder,
        CustomerSession $customerSession,
        array $data = []
    )
    {
        $this->json = $json;
        $this->urlBuilder = $urlBuilder;
        $this->customerSession = $customerSession;
        $this->loggedCustomerUrls = !empty($data['loggedCustomerUrls']) ? $data['loggedCustomerUrls'] : [];
        $this->notLoggedCustomerUrls = !empty($data['notLoggedCustomerUrls']) ? $data['notLoggedCustomerUrls'] : [];
    }

    /**
     * @param \Magento\Customer\CustomerData\Customer $subject
     * @param                                         $result
     *
     * @return mixed
     */
    public function afterGetSectionData(\Magento\Customer\CustomerData\Customer $subject, $result)
    {
        $links = $this->customerSession->isLoggedIn() ? $this->loggedCustomerUrls : $this->notLoggedCustomerUrls;
        foreach ($links as $link) {
            $result['customerTopLinks'][] = [
                'label' => __($link['label']),
                'url' => $this->urlBuilder->getUrl($link['path']),
                'handle' => !empty($link['handle']) ? $link['handle'] : null
            ];
        }
        return $result;
    }
}