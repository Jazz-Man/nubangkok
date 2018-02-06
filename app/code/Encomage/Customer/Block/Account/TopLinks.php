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

    public function getJsConfig()
    {
        return $this->json->serialize(
            [
                'logged' => [
                    ['label' => __('Log out'), 'href' => $this->urlBuilder->getUrl('customer/account/logout')],
                    ['label' => __('My account'), 'href' => $this->urlBuilder->getUrl('customer/account')]
                ],
                'notLogged' => [
                    ['label' => __('Sign in'), 'href' => $this->urlBuilder->getUrl('customer/account/login')],
                    ['label' => __('Create account'), 'href' => $this->urlBuilder->getUrl('customer/account/create')]
                ]
            ]
        );
    }
}
