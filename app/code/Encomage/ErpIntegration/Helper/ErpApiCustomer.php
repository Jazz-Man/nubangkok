<?php

namespace Encomage\ErpIntegration\Helper;

use Encomage\ErpIntegration\Logger\Logger;
use Encomage\ErpIntegration\Model\Api\ErpCreateCustomerResponse;
use Exception;
use Magento\Customer\Model\Address\Mapper as AddressMapper;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\ResourceModel\Customer as CustomerResource;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Psr\Http\Message\ResponseInterface;

/**
 * Class ErpApiCustomer.
 */
class ErpApiCustomer extends AbstractHelper
{

    /**
     * @var \Magento\Customer\Model\ResourceModel\CustomerRepository
     */
    private $customerRepository;
    /**
     * @var \Magento\Customer\Model\Address\Mapper
     */
    private $addressMapper;
    /**
     * @var \Encomage\ErpIntegration\Helper\ErpApiClient
     */
    private $erpApiClient;
    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer
     */
    private $customerResource;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    private $customerFactory;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * ErpApiCustomer constructor.
     *
     * @param \Magento\Framework\App\Helper\Context                    $context
     * @param \Magento\Customer\Model\ResourceModel\CustomerRepository $customerRepository
     * @param \Magento\Customer\Model\CustomerFactory                  $customerFactory
     * @param AddressMapper                                            $addressMapper
     * @param CustomerResource                                         $customerResource
     * @param \Encomage\ErpIntegration\Logger\Logger                   $logger
     * @param \Encomage\ErpIntegration\Helper\ErpApiClient             $erpApiClient
     */
    public function __construct(
        Context $context,
        CustomerRepository $customerRepository,
        CustomerFactory $customerFactory,
        AddressMapper $addressMapper,
        CustomerResource $customerResource,
        Logger $logger,
        ErpApiClient $erpApiClient
    ) {
        $this->customerRepository = $customerRepository;
        $this->addressMapper      = $addressMapper;
        $this->erpApiClient       = $erpApiClient;
        $this->customerResource   = $customerResource;
        $this->customerFactory    = $customerFactory;
        $this->logger             = $logger;

        parent::__construct($context);
    }

    /**
     * @param int|\Magento\Customer\Api\Data\CustomerInterface $customer
     *
     * @return bool|mixed|string
     */
    public function getCustomerErpCode($customer)
    {
        $result = false;

        try {
            if (is_numeric($customer)) {
                $customer = $this->customerRepository->getById($customer);
            }

            if (null !== $customer) {
                if ($code = $this->hasErpCustomerCode($customer)) {
                    return $code;
                }

                return $this->createCustomer($customer);
            }
        } catch (Exception $e) {
        }

        return $result;
    }

    /**
     * @param \Magento\Customer\Api\Data\CustomerInterface|null $customer
     *
     * @return bool|string
     */
    private function hasErpCustomerCode($customer)
    {
        if ((null !== $customer) && $customAttr = $customer->getCustomAttribute('erp_customer_code')) {
            return (string)$customAttr->getValue();
        }

        return false;
    }

    /**
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     *
     * @param string                                       $point
     *
     * @return mixed
     */
    public function createCustomer($customer, string $point = 'CreateCustomer')
    {
        $this->logger->info("Start ERP {$point}");

        $self = $this;

        $customer_data = $this->prepareCustomerPostData($customer);

        $response = $this->erpApiClient->postJsonData($point, $customer_data)->then(static function (
            ResponseInterface $res
        ) use ($self, $customer, $point) {
            try {
                $result = $self->erpApiClient->parseBody($res);

                $self->logger->info("{$point} Response", (array)$result);

                $erpCustomer = new ErpCreateCustomerResponse($result);

                if ($erpCustomer->isValid()) {
                    $self->updateErpCustomerCode($customer, $erpCustomer->getCustomerCode(), $point);

                    return $erpCustomer->getCustomerCode();
                }
            } catch (Exception $exception) {
            }

            return false;
        });

        return $response->wait();
    }

    /**
     * @param int|\Magento\Customer\Api\Data\CustomerInterface $customer
     *
     * @return array|bool
     */
    public function prepareCustomerPostData($customer)
    {
        $result = false;

        try {
            if (is_numeric($customer)) {
                $customer = $this->customerRepository->getById($customer);
            }

            /** @var \Magento\Customer\Model\Data\Address[] $addresses */
            $addresses = $customer->getAddresses();

            $customerData = [
                'customerTypeCode' => 'General',
                'customerBranchno' => 'Online',
                'salespersonCode'  => 'admin',
                'customerTaxid'    => 'tax1',
                'paymentTermCode'  => 'cash',
            ];

            if ( ! empty($addresses)) {
                $addresses = reset($addresses);

                $addresses_array = $this->addressMapper->toFlatArray($addresses);

                $customerName = [];

                $CountryCode = ! empty($addresses_array['country_id']) ? $addresses_array['country_id'] : null;
                $city        = ! empty($addresses_array['city']) ? $addresses_array['city'] : null;
                $telephone   = ! empty($addresses_array['telephone']) ? $addresses_array['telephone'] : null;
                $postcode    = ! empty($addresses_array['postcode']) ? $addresses_array['postcode'] : null;

                if ( ! empty($addresses_array['firstname'])) {
                    $customerName[] = $addresses_array['firstname'];
                }

                if ( ! empty($addresses_array['lastname'])) {
                    $customerName[] = $addresses_array['lastname'];
                }

                if ( ! empty($customerName)) {
                    $customerData['customerName'] = implode(' ', $customerName);
                }

                if ( ! empty($addresses_array['street'])) {
                    $customerData['customerAddress'] = implode(', ', $addresses_array['street']);
                }

                $customerData['customerAddressCity'] = $city;
                $customerData['Cityname']            = $city;

                $customerData['customerAddressCountryCode'] = $CountryCode;
                $customerData['Countrycode']                = $CountryCode;

                $customerData['customerTelephone'] = $telephone;

                $customerData['customerAddressPostCode'] = $postcode;
                $customerData['Postcode']                = $postcode;
            }

            $customerData['customerEmail'] = $customer->getEmail();

            $result = ['Customer' => $customerData];
        } catch (Exception $e) {
            $this->logger->error("CreateCustomer ERROR: {$e->getMessage()}");
        }

        return $result;
    }

    /**
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @param string                                       $customerCode
     * @param string                                       $point
     */
    public function updateErpCustomerCode($customer, string $customerCode, string $point = 'CreateCustomer'): void
    {
        $_customer = $this->customerFactory->create()->load($customer->getId());

        $customerData = $_customer->getDataModel();

        $customerData->setCustomAttribute('erp_customer_code', $customerCode);

        $_customer->updateData($customerData);

        try {
            $this->customerResource->save($_customer);
        } catch (Exception $e) {
            $this->logger->error("{$point} ERROR: {$e->getMessage()}");
        }
    }
}
