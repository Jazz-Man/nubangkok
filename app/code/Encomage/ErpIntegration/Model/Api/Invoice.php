<?php
namespace Encomage\ErpIntegration\Model\Api;

use Zend\Http\Request as HttpRequest;
use Magento\Framework\Serialize\Serializer\Json as SerializerJson;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Customer\Model\Session as CustomerSession;
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
     * @var CustomerSession
     */
    private $customerSession;
    /**
     * @var SerializerJson
     */
    private $json;

    /**
     * Invoice constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param CustomerRepository $customerRepository
     * @param SerializerJson $json
     * @param Customer $erpCustomer
     * @param CustomerSession $customerSession
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        CustomerRepository $customerRepository,
        SerializerJson $json,
        Customer $erpCustomer,
        CustomerSession $customerSession
    )
    {
        parent::__construct($scopeConfig, $json);
        $this->customerRepository = $customerRepository;
        $this->erpCustomer = $erpCustomer;
        $this->customerSession = $customerSession;
        $this->json = $json;
    }

    /**
     * @param $order
     * @return array|bool|float|int|mixed|null|string
     * @throws \Exception
     */
    public function createInvoice($order)
    {
        $this->setApiLastPoint('createInvoice');
        $this->setApiMethod(HttpRequest::METHOD_GET);
        $data = $this->_prepareInvoiceData($order);
        $this->setAdditionalDataContent($data);
        $result = $this->sendApiRequest();
        return $result;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return mixed
     */
    protected function _prepareInvoiceData(\Magento\Sales\Model\Order $order)
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
        $data[$fieldName]['customerTaxid'] = 'tax1';
        $data[$fieldName]['customerBranchNo'] = 'Online';
        $data[$fieldName]['salespersonCode'] = 'admin';
        $iterator = 0;
        
        foreach ($order->getItems() as $item) {
            if ($order->getRedeemNupoints()) {
                $data[$fieldName][$productsFieldName][$iterator]['productCode'] = 'Redeem'.$order->getRedeemNupoints()->getMoneyToRedeem();
                $data[$fieldName][$productsFieldName][$iterator]['quantity'] = 1;
                $data[$fieldName][$productsFieldName][$iterator]['warehouseCode'] = 'WH_ON';
                $order->setData('redeem_nupoints', false);
                $iterator ++;
                continue;
            }
            if ($item->getProductType() == 'simple') {
                $discount = $item->getParentItem()->getDiscountPercent();
                $data[$fieldName][$productsFieldName][$iterator]['productCode'] = $item->getSku();
                $data[$fieldName][$productsFieldName][$iterator]['quantity'] = $item->getQtyOrdered();
                $data[$fieldName][$productsFieldName][$iterator]['warehouseCode'] = 'WH_ON';
                $data[$fieldName][$productsFieldName][$iterator]['discountText'] = $discount . '%';
                $iterator ++;
            }
        }
        $paymentInfo = $this->_getPaymentInfo($order);
        $data[$fieldName] = array_merge($data[$fieldName], $paymentInfo[$fieldName]);

        return $data;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return mixed
     */
    protected function _getPaymentInfo(\Magento\Sales\Model\Order $order)
    {
        $data['Order']['linePayments'][0]['paymentMethodCode'] = $order->getPayment()->getMethod();
        $data['Order']['linePayments'][0]['amount'] = $order->getGrandTotal();
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
        } elseif ($customer->getId()) {
            $this->erpCustomer->createOrUpdateCustomer($customerId);
            $this->_getCustomerCodeById($customer->getId());
        }
        return null;
    }

    /**
     * @param $point
     * @return mixed
     */
    public function setApiLastPoint($point)
    {
        return $this->apiLastPoint = $point;
    }

    /**
     * @param string $method
     * @return string
     */
    public function setApiMethod($method = HttpRequest::METHOD_GET)
    {
        return $this->apiMethod = $method;
    }

    /**
     * @param array $data
     * @return array
     */
    public function setAdditionalDataUrl(array $data = [])
    {
        return $this->additionalDataUrl = $data;
    }

    /**
     * @param array $content
     * @return array
     */
    public function setAdditionalDataContent(array $content = [])
    {
        return $this->additionalDataContent = $content;
    }
}