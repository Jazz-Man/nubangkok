<?php
namespace Encomage\Nupoints\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface StoriesSearchResultsInterface
 * @package Encomage\Nupoints\Api\Data
 */
interface NupointsSearchResultsInterface extends SearchResultsInterface
{
    /**
     * @return \Encomage\Nupoints\Api\Data\NupointsInterface[]
     */
    public function getItems();

    /**
     * @param \Encomage\Nupoints\Api\Data\NupointsInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}