<?php

namespace Encomage\Customer\Model;

use Encomage\Nupoints\Api\NupointsRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Framework\Reflection\DataObjectProcessor;

/**
 * Class Customer
 * @package Encomage\Customer\Model
 */
class Customer extends \Magento\Customer\Model\Customer
{
    private $nupointsRepository;

    /**
     * Customer constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Eav\Model\Config $config
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Customer\Model\ResourceModel\Customer $resource
     * @param \Magento\Customer\Model\Config\Share $configShare
     * @param \Magento\Customer\Model\AddressFactory $addressFactory
     * @param \Magento\Customer\Model\ResourceModel\Address\CollectionFactory $addressesFactory
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param GroupRepositoryInterface $groupRepository
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param CustomerInterfaceFactory $customerDataFactory
     * @param DataObjectProcessor $dataObjectProcessor
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param CustomerMetadataInterface $metadataService
     * @param \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
     * @param NupointsRepositoryInterface $nupointsRepository
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Eav\Model\Config $config,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Customer\Model\ResourceModel\Customer $resource,
        \Magento\Customer\Model\Config\Share $configShare,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Magento\Customer\Model\ResourceModel\Address\CollectionFactory $addressesFactory,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        GroupRepositoryInterface $groupRepository,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        CustomerInterfaceFactory $customerDataFactory,
        DataObjectProcessor $dataObjectProcessor,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        CustomerMetadataInterface $metadataService,
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry,
        NupointsRepositoryInterface $nupointsRepository,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        parent::__construct(
            $context,
            $registry,
            $storeManager,
            $config,
            $scopeConfig,
            $resource,
            $configShare,
            $addressFactory,
            $addressesFactory,
            $transportBuilder,
            $groupRepository,
            $encryptor,
            $dateTime,
            $customerDataFactory,
            $dataObjectProcessor,
            $dataObjectHelper,
            $metadataService,
            $indexerRegistry,
            $resourceCollection,
            $data
        );
        $this->nupointsRepository = $nupointsRepository;
    }

    /**
     * @return \Encomage\Nupoints\Model\Nupoints mixed
     */
    public function getNupointItem()
    {
        if (!$this->hasData('nupoint_item')) {
            $redeemItem = $this->nupointsRepository->getByCustomerId($this->getId());
            $this->setData('nupoint_item', $redeemItem);
        }
        return $this->getData('nupoint_item');
    }

    /**
     * @return mixed
     */
    public function getLineId()
    {
        return $this->getDataModel()->getLineId();
    }
}