<?php

namespace Encomage\DropdownFields\Model;
/**
 * Class Country
 * @package Encomage\DropdownFields\Model
 */

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * Class Country
 * @package Encomage\DropdownFields\Model
 */
class Country extends AbstractModel implements IdentityInterface
{
    const CACHE_TAG = 'encomage_dropdownfields_country';

    /**
     * @var string
     */
    protected $_cacheTag = 'encomage_dropdownfields_country';

    /**
     * @var string
     */
    protected $_eventPrefix = 'encomage_dropdownfields_country';


    protected function _construct()
    {
        $this->_init('Encomage\DropdownFields\Model\ResourceModel\Country');
    }

    /**
     * @return array|string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}