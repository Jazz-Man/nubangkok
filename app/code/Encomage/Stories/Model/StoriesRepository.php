<?php
namespace Encomage\Stories\Model;

use Encomage\Stories\Model\ResourceModel\Stories as ResourceStories;
use Encomage\Stories\Model\StoriesFactory;
use Encomage\Stories\Api\StoriesRepositoryInterface;
use Encomage\Stories\Api\Data\StoriesInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;

/**
 * Class StoriesRepository
 * @package Encomage\Stories\Model
 */
class StoriesRepository implements StoriesRepositoryInterface
{
    /**
     * @var ResourceStories
     */
    private $resource;
    /**
     * @var \Encomage\Stories\Model\StoriesFactory
     */
    private $storiesFactory;
    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    public function __construct(
        ResourceStories $resourceStories,
        CollectionProcessorInterface $collectionProcessor,
        StoriesFactory $storiesFactory
    )
    {

        $this->resource = $resourceStories;
        $this->storiesFactory = $storiesFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        // TODO: Implement getList() method.
    }
    
    public function save(StoriesInterface $stories)
    {
        try {
            $this->resource->save($stories);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $stories;
    }

    public function getById($itemId)
    {
        $stories = $this->storiesFactory->create();
        $this->resource->load($stories, $itemId);
        if (!$stories->getId()) {
            throw new NoSuchEntityException(__('Item id "%1" does not exist.', $itemId));
        }
        return $stories;
    }

    public function delete(StoriesInterface $stories)
    {
        try {
            $this->resource->delete($stories);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    public function deleteById($itemId)
    {
        return $this->delete($this->getById($itemId));
    }

    public function getByCustomerId($customerId)
    {
        $stories = $this->storiesFactory->create();
        $this->resource->load($stories, $customerId, 'customer_id');
        if (!$stories->getId()) {
            $stories->setCustomerId($customerId);
        }
        return $stories;
    }

    public function deleteByCustomerId($customerId)
    {
        return $this->delete($this->getByCustomerId($customerId));
    }
}