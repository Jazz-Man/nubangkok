<?php

namespace Encomage\ErpIntegration\Helper;

use Encomage\ErpIntegration\Logger\Logger;
use Encomage\ErpIntegration\Model\Api\ErpCreateCustomerResponse;
use Exception;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
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
     * @param \Magento\Customer\Model\Address\Mapper                   $addressMapper
     * @param \Magento\Customer\Model\ResourceModel\Customer           $customerResource
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
        $this->logger = $logger;

        parent::__construct($context);
    }

    /**
     * @param int|\Magento\Customer\Api\Data\CustomerInterface $customer
     *
     * @return bool
     */
    public function getCustomerErpCode($customer)
    {
        $result = false;

        try {
            if (is_numeric($customer)) {
                $customer = $this->customerRepository->getById($customer);
            }

            if ($customer !== null){

                if ($code = $this->hasErpCustomerCode($customer)) {
                    return $code;
                }

                $this->logger->info('Start ERP CreateCustomer');

                $clientHandler = $this->erpApiClient->getClient()->getConfig('handler');

                $self = $this;

                $tapMiddleware = Middleware::tap(static function (Request $request) use ($self) {

                    /** @var \GuzzleHttp\Psr7\Uri $uri */
                    $uri = $request->getUri();

                    $Body = $self->erpApiClient->jsonDecode($request->getBody(), true);

                    $self->logger->info('CreateCustomer Request Uri:', [(string) $uri]);
                    $self->logger->info('CreateCustomer Request Body:', $Body);
                });


                $response = $this->erpApiClient->postJsonData('CreateCustomer', $this->prepareCustomerPostData($customer), [
                    'handler' => $tapMiddleware($clientHandler),
                ])->then(static function (ResponseInterface $res) use ($self, $customer) {

                    try{
                        $result = $self->erpApiClient->parseBody($res);

                        $self->logger->info('CreateCustomer Response', (array) $result);

                        $erpCustomer = new ErpCreateCustomerResponse($result);

                        if ( $erpCustomer->isValid()) {

                            $self->updateErpCustomerCode($customer, $erpCustomer->getCustomerCode());

                            return $erpCustomer->getCustomerCode();
                        }

                    }catch (Exception $exception){

                    }

                    return false;

                });

                return $response->wait();
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

        if (($customer !== null) && $customAttr = $customer->getCustomAttribute('erp_customer_code')) {
            return (string)$customAttr->getValue();
        }

        return false;
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
     */
    private function updateErpCustomerCode($customer, string $customerCode): void
    {

        $_customer = $this->customerFactory->create()->load($customer->getId());

        $customerData = $_customer->getDataModel();

        $customerData->setCustomAttribute('erp_customer_code', $customerCode);

        $_customer->updateData($customerData);

        try {
            $this->customerResource->save($_customer);
        } catch (Exception $e) {
            $this->logger->error("CreateCustomer ERROR: {$e->getMessage()}");
        }

    }
}
