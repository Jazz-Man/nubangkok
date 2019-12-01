<?php

namespace Encomage\Customer\Block\Account;

use Magento\Customer\Block\Account\Link;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class TopLinks
 *
 * @package Encomage\Customer\Block\Account
 */
class TopLinks extends Link
{


    /**
     * @return bool|false|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getConfig()
    {

        $json = new Json();

        return $json->serialize([
            'websiteId' => $this->_storeManager->getWebsite()->getId(),
        ]);
    }
}
