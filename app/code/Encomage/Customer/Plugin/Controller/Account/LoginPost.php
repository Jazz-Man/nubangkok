<?php

namespace Encomage\Customer\Plugin\Controller\Account;

class LoginPost
{

    protected $url;


    public function __construct(
        \Magento\Framework\UrlInterface $url
    )
    {
        $this->url = $url;
    }

    public function afterExecute(
        \Magento\Customer\Controller\Account\CreatePost $subject,
        $resultRedirect
    ) {
        $resultRedirect->setUrl($this->url->getUrl('/'));
        return $resultRedirect;
    }

}
