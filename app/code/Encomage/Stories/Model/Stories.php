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
     * @return mixed
     */
    public function getContent()
    {
        return $this->_getData(self::CONTENT);
    }

    /**
     * @param $content
     * @return $this
     */
    public function setContent($content)
    {
        return $this->setData(self::CONTENT, $content);
    }

    /**
     * @return mixed
     */
    public function getIsApprove()
    {
        return $this->_getData(self::IS_APPROVE);
    }

    /**
     * @param $status
     * @return $this
     */
    public function setIsApprove($status)
    {
        return $this->setData(self::IS_APPROVE, $status);
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->_getData(self::CREATED_AT);
    }

    /**
     * @param $date
     * @return $this
     */
    public function setCreatedAt($date)
    {
        return $this->setData(self::CREATED_AT, $date);
    }

    /**
     * @return mixed
     */
    public function getImagePath()
    {
        return $this->_getData(self::IMAGE_PATH);
    }

    /**
     * @param $path
     * @return $this
     */
    public function setImagePath($path)
    {
        return $this->setData(self::IMAGE_PATH, $path);
    }
}