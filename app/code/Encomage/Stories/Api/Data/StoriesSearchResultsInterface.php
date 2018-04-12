<?php
namespace Encomage\Stories\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface StoriesSearchResultsInterface
 * @package Encomage\Stories\Api\Data
 */
interface StoriesSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get blocks list.
     *
     * @return \Magento\Cms\Api\Data\BlockInterface[]
     */
    public function getItems();

    /**
     * Set blocks list.
     *
     * @param \Magento\Cms\Api\Data\BlockInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}