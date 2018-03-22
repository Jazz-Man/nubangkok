<?php

namespace Encomage\Stories\Api\Data;

/**
 * Interface StoriesInterface
 * @package Encomage\Stories\Api\Data
 */
interface StoriesInterface
{
    const ITEM_ID = 'entity_id',
        CUSTOMER_ID = 'customer_id',
        IS_APPROVE = 'is_approved',
        CONTENT = 'content',
        CREATED_AT = 'created_at',
        IMAGE_PATH = 'image_path';

    /**
     * @return int
     */
    public function getItemId();

    /**
     * @return int
     */
    public function getCustomerId();

    /**
     * @param $id
     * @return mixed
     */
    public function setItemId($id);

    /**
     * @param $customerId
     * @return mixed
     */
    public function setCustomerId($customerId);

    /**
     * @return mixed
     */
    public function getIsApprove();

    /**
     * @param $status
     * @return mixed
     */
    public function setIsApprove($status);

    /**
     * @return mixed
     */
    public function getContent();

    /**
     * @param $content
     * @return mixed
     */
    public function setContent($content);

    /**
     * @return mixed
     */
    public function getCreatedAt();

    /**
     * @param $date
     * @return mixed
     */
    public function setCreatedAt($date);

    /**
     * @return mixed
     */
    public function getImagePath();

    /**
     * @param $path
     * @return mixed
     */
    public function setImagePath($path);
}