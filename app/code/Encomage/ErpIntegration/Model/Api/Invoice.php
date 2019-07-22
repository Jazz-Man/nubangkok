<?php
namespace Encomage\ErpIntegration\Model\Api;

use Zend\Http\Request as HttpRequest;
use Magento\Framework\Serialize\Serializer\Json as SerializerJson;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Sales\Model\ResourceModel\Order as OrderResource;
use Magento\Sales\Model\Order;

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
     * @var SerializerJson
     */
    private $json;
    /**
     * @var OrderResource
     */
    private $orderResource;

    /**
     * Invoice constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param CustomerRepository $customerRepository
     * @param SerializerJson $json
     * @param Customer $erpCustomer
     * @param OrderResource $orderResource
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        CustomerRepository $customerRepository,
        SerializerJson $json,
        Customer $erpCustomer,
        OrderResource $orderResource
    )
    {
        parent::__construct($scopeConfig, $json);
        $this->customerRepository = $customerRepository;
        $this->erpCustomer = $erpCustomer;
        $this->json = $json;
        $this->orderResource = $orderResource;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return array|bool|float|int|mixed|null|string
     * @throws \Exception
     */
    public function createInvoice(Order $order)
    {
        $this->setApiLastPoint('createInvoice');
        $this->setApiMethod();
        $data = $this->_prepareInvoiceData($order);
        $this->setAdditionalDataContent($data);
        $result = $this->sendApiRequest();
        if (array_key_exists('returnResult', $result) && $result['returnResult']) {
            $order->addCommentToStatusHistory(__('Sent invoice to ERP. DocNo: %1. RecId: %2', $result['DocNo'], $result['recId']), $order->getStatus());
            $this->orderResource->save($order);
        } else {
            $order->addCommentToStatusHistory(__("Invoice wasn't sent to ERP system. \n ERROR: ". $result['errorMessage']), parent::ORDER_STATUS_PENDING_NOT_SENT);
            $this->orderResource->save($order);
        }
        return $result;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _prepareInvoiceData(Order $order)
    {
        $fieldName = 'Order';
        $productsFieldName = 'lineItems';

        $shippingAddress = $order->getShippingAddress();

        if ($shippingAddress !== null){
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
                if ($item->getProductType() === 'simple') {

                    $parentItem = $item->getParentItem();

                    if ($parentItem !== null){
                        $discount = $parentItem->getDiscountPercent();
                        $data[$fieldName][$productsFieldName][$iterator]['productCode'] = $item->getSku();
                        $data[$fieldName][$productsFieldName][$iterator]['quantity'] = $item->getQtyOrdered();
                        $data[$fieldName][$productsFieldName][$iterator]['warehouseCode'] = $this->_getWarehouseCode();
                        $data[$fieldName][$productsFieldName][$iterator]['discountText'] = $discount . '%';
                        $iterator ++;
                    }
                }
            }
            if ($order->getRedeemAmount()) {
                $data[$fieldName][$productsFieldName][$iterator]['productCode'] = 'Redeem'.$order->getRedeemAmount();
                $data[$fieldName][$productsFieldName][$iterator]['quantity'] = 1;
                $data[$fieldName][$productsFieldName][$iterator]['warehouseCode'] = $this->_getWarehouseCode();
            }
            $paymentInfo = $this->_getPaymentInfo($order);
            $data[$fieldName] = array_merge($data[$fieldName], $paymentInfo[$fieldName]);

            return $data;
        }

        return false;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return mixed
     */
    protected function _getPaymentInfo(Order $order)
    {
        $data['Order']['linePayments'][0]['paymentMethodCode'] = $order->getPayment()->getMethod();
        $data['Order']['linePayments'][0]['amount'] = $order->getBaseSubtotalInclTax() + $order->getBaseShippingAmount();
        return $data;
    }

    /**
     * @param int $customerId
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Exception
     */
    protected function _getCustomerCodeById($customerId)
    {
        $customer = $this->customerRepository->getById($customerId);
        if ($customer->getId() && $customAttr = $customer->getCustomAttribute('erp_customer_code')) {
            return $customAttr->getValue();
        }

        if ($customer->getId()) {
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