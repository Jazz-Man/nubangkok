<?php
namespace Encomage\Stories\Model;

use Encomage\Stories\Api\Data\StoriesInterface;
use Magento\Framework\Model\AbstractModel;

class Stories extends AbstractModel implements StoriesInterface
{
    /**
     * class construct
     */
    protected function _construct()
    {
        $this->_init(\Encomage\Stories\Model\ResourceModel\Stories::class);
    }

    /**
     * @return mixed
     */
    public function getItemId()
    {
        return $this->_getData(self::ITEM_ID);
    }

    /**
     * @return mixed
     */
    public function getCustomerId()
    {
        return $this->_getData(self::CUSTOMER_ID);
    }

    /**
     * @param $customerId
     * @return $this
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * @param $id
     * @return $this
     */
    public function setItemId($id)
    {
        return $this->setData(self::ITEM_ID, $id);
    }

    /**
     * @param $story
     * @return $this
     */
    public function setStory($story)
    {
        return $this->setData($story);
    }
}