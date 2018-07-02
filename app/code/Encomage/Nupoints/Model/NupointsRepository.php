<?php
namespace Encomage\Nupoints\Model;

use Encomage\Nupoints\Api\NupointsRepositoryInterface;
use Encomage\Nupoints\Api\Data\NupointsInterface;
use Encomage\Nupoints\Model\ResourceModel\Nupoints as ResourceNupoints;
use Encomage\Nupoints\Model\NupointsFactory;
use Encomage\Nupoints\Model\ResourceModel\Nupoints\CollectionFactory as NupointsCollectionFactory;
use Encomage\Nupoints\Api\Data\NupointsSearchResultsInterfaceFactory;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Class NupointsRepository
 * @package Encomage\Nupoints\Model
 */
class NupointsRepository implements NupointsRepositoryInterface
{
    /**
     * @var ResourceNupoints
     */
    private $resourceNupoints;

    /**
     * @var \Encomage\Nupoints\Model\NupointsFactory
     */
    private $nupointsFactory;
    
    /**
     * @var NupointsCollectionFactory
     */
    private $nupointsCollectionFactory;
    
    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;
    
    /**
     * @var NupointsSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;
    
    /**
     * @var CustomerRepository
     */
    private $customerRepository;
    
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * NupointsRepository constructor.
     * @param ResourceNupoints $resourceNupoints
     * @param \Encomage\Nupoints\Model\NupointsFactory $nupointsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param NupointsSearchResultsInterfaceFactory $searchResultsFactory
     * @param NupointsCollectionFactory $nupointsCollectionFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CustomerRepository $customerRepository
     */
    public function __construct(
        ResourceNupoints $resourceNupoints,
        NupointsFactory $nupointsFactory,
        CollectionProcessorInterface $collectionProcessor,
        NupointsSearchResultsInterfaceFactory $searchResultsFactory,
        NupointsCollectionFactory $nupointsCollectionFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CustomerRepository $customerRepository
    )
    {
        $this->nupointsCollectionFactory = $nupointsCollectionFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->customerRepository = $customerRepository;
        $this->resourceNupoints = $resourceNupoints;
        $this->nupointsFactory = $nupointsFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(NupointsInterface $nupoint)
    {
        try {
            $this->resourceNupoints->save($nupoint);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $nupoint;
    }

    /**
     * @param $itemId
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getById($itemId)
    {
        $nupoint = $this->nupointsFactory->create();
        $this->resourceNupoints->load($nupoint, $itemId);
        if (!$nupoint->getId()) {
            throw new NoSuchEntityException(__('Item id "%1" does not exist.', $itemId));
        }
        return $nupoint;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var \Encomage\Nupoints\Model\ResourceModel\Nupoints\Collection $collection */
        $collection = $this->nupointsCollectionFactory->create();

        $this->collectionProcessor->process($searchCriteria, $collection);

        /** @var \Encomage\Nupoints\Api\Data\NupointsSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @param NupointsInterface $nupoint
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(NupointsInterface $nupoint)
    {
        try {
            $this->resourceNupoints->delete($nupoint);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * @param $itemId
     * @return bool|mixed
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($itemId)
    {
        return $this->delete($this->getById($itemId));
    }

    /**
     * {@inheritdoc}
     */
    public function getByCustomerId($customerId)
    {
        $nupoint = $this->nupointsFactory->create();
        $this->resourceNupoints->load($nupoint, $customerId, 'customer_id');
        if (!$nupoint->getId()) {
           $nupoint->setCustomerId($customerId);
        }
        return $nupoint;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteByCustomerId($customerId)
    {
        return $this->delete($this->getByCustomerId($customerId));
    }

    /**
     * {@inheritdoc}
     */
    public function changeNupointsCount($options, $method)
    {
        $customerId = $this->_getCustomerIdByCode($options['customer_code']);
        $item = $this->getByCustomerId($customerId);
        $nupoint = (int)$item->getNupoints();
        switch ($method){
            case $method == 'add':
                $nupoint += (int)$options['nupoints'];
                break;
            case $method == 'update':
                $nupoint = (int)$options['nupoints'];
                break;
            case $method == 'subtract':
                $nupoint -= (int)$options['nupoints'];
                break;
        }
        $this->save($item->setNupoints($nupoint));
        return $item;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getNupointsByCustomerCode($customerCode)
    {
        $nupoint = $this->nupointsFactory->create();
        $customerId = $this->_getCustomerIdByCode($customerCode);
        $this->resourceNupoints->load($nupoint, $customerId, 'customer_id');
        if (!$nupoint->getId()) {
            $nupoint->setCustomerId($customerId);
        }
        return $nupoint;
    }

    /**
     * @param $customerCode
     * @return int|null
     */
    protected function _getCustomerIdByCode($customerCode)
    {
        $criteria = $this->searchCriteriaBuilder->addFilter('erp_customer_code', $customerCode, 'eq')->create();
        $customer = $this->customerRepository->getList($criteria);
        if ($customer->getTotalCount() > 0) {
            foreach ($customer->getItems() as $item) {
                if ($item->getId()) {
                    $customer = $item;
                    break;
                }
            }
            return $customer->getId();
        }
    }
}