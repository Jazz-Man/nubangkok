<?php

namespace Encomage\Customer\Block\Account;

use Magento\Framework\UrlInterface;
use Magento\Framework\Serialize\Serializer\Json;

class TopLinks extends \Magento\Customer\Block\Account\Link
{

    private $urlBuilder;
    private $json;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Url $customerUrl,
        array $data = [],
        UrlInterface $urlBuilder,
        Json $json

    )
    {
        parent::__construct($context, $customerUrl, $data);
        $this->urlBuilder = $urlBuilder;
        $this->json = $json;
    }

    public function getConfig()
    {
        return $this->json->serialize(
            [
                'websiteId' => $this->_storeManager->getWebsite()->getId()
            ]
        );
    }
}
