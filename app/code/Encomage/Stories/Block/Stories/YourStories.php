<?php
namespace Encomage\Stories\Block\Stories;

use Magento\Framework\View\Element\Template;
use Encomage\Stories\Model\StoriesRepositoryFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Encomage\Stories\Api\StoriesRepositoryInterface;

class YourStories extends Template
{
    /**
     * @var StoriesRepositoryFactory
     */
    private $storiesRepositoryFactory;
    /**
     * @var FilterBuilder
     */
    private $filterBuilder;
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var StoriesRepositoryInterface
     */
    private $storiesRepository;

    /**
     * YourStories constructor.
     * @param Template\Context $context
     * @param StoriesRepositoryFactory $storiesRepositoryFactory
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param StoriesRepositoryInterface $storiesRepository
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        StoriesRepositoryFactory $storiesRepositoryFactory,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        StoriesRepositoryInterface $storiesRepository,
        array $data
    )
    {
        $this->storiesRepository = $storiesRepository;
        $this->storiesRepositoryFactory = $storiesRepositoryFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        parent::__construct($context, $data);
    }

    public function storiesCollection()
    {
        $this->searchCriteriaBuilder->addFilters(
            [
                $this->filterBuilder
                    ->setField(\Encomage\Stories\Api\Data\StoriesInterface::IS_APPROVE)
                    ->setValue([1])
                    ->create()
            ]
        );
        $searchResults = $this->storiesRepository->getList($this->searchCriteriaBuilder->create());
        return $searchResults;
    }
}