<?php

namespace Encomage\Customer\Plugin\CustomerData;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\UrlInterface;
use Magento\Customer\Model\Session as CustomerSession;

class Customer
{

    private $json;
    private $urlBuilder;
    private $customerSession;
    private $loggedCustomerUrls;
    private $notLoggedCustomerUrls;

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
        $this->loggedCustomerUrls = (isset($data['loggedCustomerUrls'])) ? $data['loggedCustomerUrls'] : [];
        $this->notLoggedCustomerUrls = (isset($data['notLoggedCustomerUrls'])) ? $data['notLoggedCustomerUrls'] : [];
    }

    public function afterGetSectionData(\Magento\Customer\CustomerData\Customer $subject, $result)
    {
        $links = ($this->customerSession->isLoggedIn()) ? $this->loggedCustomerUrls : $this->notLoggedCustomerUrls;
        foreach ($links as $link) {
            $result['customerTopLinks'][] = [
                'label' => __($link['label']),
                'url' => $this->urlBuilder->getUrl($link['path']),
                'handle' => (isset($link['handle'])) ? $link['handle'] : null
            ];
        }
        return $result;
    }
}