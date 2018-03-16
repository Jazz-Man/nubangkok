<?php

namespace Encomage\Theme\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const FACEBOOK_SHARE_LINK = 'http://www.facebook.com/sharer/sharer.php';

    public function getFacebookShareLink(string $shareUrl)
    {
        return self::FACEBOOK_SHARE_LINK . '?u=' . $shareUrl;
    }
}