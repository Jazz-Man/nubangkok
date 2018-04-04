<?php
namespace Encomage\Stories\Block\Stories;

use Magento\Framework\View\Element\Template;
use Encomage\Stories\Model\StoriesRepositoryFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Encomage\Stories\Api\StoriesRepositoryInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\ResourceModel\Customer as CustomerResource;

class YourStories extends Template
{
    const IMAGE_MEDIA_PATH = 'pub/media/';
    const STRING_COUNT_CHARACTERS = 360;
    
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
     * @var CustomerFactory
     */
    private $customerFactory;
    /**
     * @var CustomerResource
     */
    private $customerResource;

    /**
     * YourStories constructor.
     * @param Template\Context $context
     * @param StoriesRepositoryFactory $storiesRepositoryFactory
     * @param FilterBuilder $filterBuilder
     * @param CustomerResource $customerResource
     * @param CustomerFactory $customerFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param StoriesRepositoryInterface $storiesRepository
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        StoriesRepositoryFactory $storiesRepositoryFactory,
        FilterBuilder $filterBuilder,
        CustomerResource $customerResource,
        CustomerFactory $customerFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        StoriesRepositoryInterface $storiesRepository,
        array $data
    )
    {
        $this->customerResource = $customerResource;
        $this->customerFactory = $customerFactory;
        $this->storiesRepository = $storiesRepository;
        $this->storiesRepositoryFactory = $storiesRepositoryFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        parent::__construct($context, $data);
    }

    /**
     * @return mixed
     */
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

    /**
     * @param $path
     * @return bool|string
     */
    public function getImage($path)
    {
        if (!empty($path)
            && ($this->getMediaDirectory()->isExist($path)
                || $this->getMediaDirectory()->isReadable($path))
        ) {
            return $this->getBaseUrl() . self::IMAGE_MEDIA_PATH . $path;
        }
        return false;
    }

    /**
     * @param $content
     * @return bool|string
     */
    public function getResponsibleContent($content)
    {
        if (!empty($content)) {
            $count = iconv_strlen($content);
            if ($count > self::STRING_COUNT_CHARACTERS) {
                $content = iconv_substr(trim($content), 0, self::STRING_COUNT_CHARACTERS - 30);
            }
            return $content;
        }
        return false;
    }

    /**
     * @param null $date
     * @return string
     */
    public function newDate($date = null)
    {
        $newDate = new \DateTime($date);
        return $newDate->format('m/d/Y');
    }

    /**
     * @param $customerId
     * @return mixed
     */
    public function getCustomerNameById($customerId)
    {
        $customer = $this->customerFactory->create();
        $this->customerResource->load($customer, $customerId);
        return $customer->getName();
    }
}