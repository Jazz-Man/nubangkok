<?php
namespace Encomage\Careers\Model;
class Careers extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'encomage_careers';

    protected $_cacheTag = 'encomage_careers';

    protected $_eventPrefix = 'encomage_careers';

    protected function _construct()
    {
        $this->_init('Encomage\Careers\Model\ResourceModel\Careers');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}