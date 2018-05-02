<?php
namespace Encomage\ErpIntegration\Model\Api;

use Zend\Http\Request as HttpRequest;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Customer\Model\ResourceModel\CustomerRepository;

/**
 * Class Invoice
 * @package Encomage\ErpIntegration\Model\Api
 */
class Invoice extends Request
{
    /**
     * @var CustomerRepository
     */
    private $customerRepository;
    /**
     * @var Customer
     */
    private $erpCustomer;

    /**
     * Invoice constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param CustomerRepository $customerRepository
     * @param Customer $erpCustomer
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        CustomerRepository $customerRepository,
        Customer $erpCustomer
    )
    {
        parent::__construct($scopeConfig);
        $this->customerRepository = $customerRepository;
        $this->erpCustomer = $erpCustomer;
    }

    /**
     * @param $order
     * @return array|mixed
     */
    public function createInvoice($order)
    {
        $this->_setApiLastPoint('createInvoice');
        $this->_setApiMethod(HttpRequest::METHOD_GET);

        $data = $this->_prepareInvoiceData($order);
        $this->_setAdditionalDataContent($data);

        $result = $this->sendApiRequest();
        if (is_object($result)) {
            $result = get_object_vars($result);
        }
        return $result;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return mixed
     */
    protected function _prepareInvoiceData($order)
    {
        $fieldName = 'Order';
        $productsFieldName = 'lineItems';

        $shippingAddress = $order->getShippingAddress();
        $street = implode(',', $shippingAddress->getStreet());
        $customerCode = $this->_getCustomerCodeById($order->getCustomerId());

        $data[$fieldName]['CustomerCode'] = $customerCode;
        $data[$fieldName]['customerName'] = $order->getCustomerFirstname() . ' ' . $order->getCustomerLastname();
        $data[$fieldName]['customerAddress'] = $shippingAddress->getCity() . ' ' . trim($street, ' ') . ' ' . $shippingAddress->getPostcode();
        $data[$fieldName]['customerTelephone'] = $shippingAddress->getTelephone();
        $data[$fieldName]['customerTaxid'] = 0;
        $data[$fieldName]['customerBranchNo'] = 0;
        $data[$fieldName]['salespersonCode'] = 'admin';
        $iterator = 0;
        foreach ($order->getItems() as $item) {
            if ($item->getProductType() == 'simple') {
                $discount = $item->getParentItem()->getDiscountPercent();
                $data[$fieldName][$productsFieldName][$iterator]['productCode'] = $item->getSku();
                $data[$fieldName][$productsFieldName][$iterator]['quantity'] = $item->getQtyOrdered();
                $data[$fieldName][$productsFieldName][$iterator]['warehouseCode'] = 'WH_ON';
                $data[$fieldName][$productsFieldName][$iterator]['discountText'] = $discount . '%';
                $iterator ++;
            }
            if ($item->getProductType() == 'virtual') {
                $data[$fieldName][$productsFieldName][$iterator]['productCode'] = $item->getSku();
                $data[$fieldName][$productsFieldName][$iterator]['quantity'] = $item->getQtyOrdered();
                $data[$fieldName][$productsFieldName][$iterator]['warehouseCode'] = 'WH_ON';
                $data[$fieldName][$productsFieldName][$iterator]['discountText'] = $item->getDiscountPercent();
                $iterator ++;
            }
        }
        $data[$fieldName]['linePayments']['paymentMethodCode'] = $order->getPayment()->getMethod();
        $data[$fieldName]['linePayments']['amount'] = $order->getGrandTotal();

        return $data;
    }

    /**
     * @param $customerId
     * @return mixed
     */
    protected function _getCustomerCodeById($customerId)
    {
        $customer = $this->customerRepository->getById($customerId);
        if ($customer->getId() && $customAttr = $customer->getCustomAttribute('erp_customer_code')) {
            return $customAttr->getValue();
        } else if ($customer->getId()) {
            $this->erpCustomer->createOrUpdateCustomer($customer);
            $this->_getCustomerCodeById($customer->getId());
        }
        return null;
    }

    /**
     * @param $point
     * @return mixed
     */
    protected function _setApiLastPoint($point)
    {
        return $this->apiPoint = $point;
    }

    /**
     * @param string $method
     * @return string
     */
    protected function _setApiMethod($method = HttpRequest::METHOD_GET)
    {
        return $this->apiMethod = $method;
    }

    /**
     * @param array $data
     * @return array
     */
    protected function _setAdditionalDataUrl(array $data = [])
    {
        return $this->additionalDataUrl = $data;
    }

    /**
     * @param array $content
     * @return array
     */
    protected function _setAdditionalDataContent(array $content = [])
    {
        return $this->additionalDataContent = $content;
    }
}