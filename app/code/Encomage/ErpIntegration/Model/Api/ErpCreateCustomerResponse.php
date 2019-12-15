<?php


namespace Encomage\ErpIntegration\Model\Api;


/**
 * Class CreateCustomerResponse
 *
 * @package Encomage\ErpIntegration\Model\Api\Customer
 */
class ErpCreateCustomerResponse extends Filter
{
    /**
     * @var string
     */
    public $customerCode;
    /**
     * @var string
     */

    public $customerName;
    /**
     * @var string
     */
    public $customerInvoiceAddress;
    /**
     * @var string
     */
    public $infoLastUpdate;
    /**
     * @var string
     */
    public $infoLastUpdateTime;
    /**
     * @var string
     */
    public $infoLastUpdateBy;


    /**
     * @return string
     */
    public function getCustomerCode(): string
    {
        return $this->cleanStringProp($this->customerCode);
    }

    /**
     * @return string
     */
    public function getCustomerName(): string
    {
        return $this->cleanStringProp($this->customerName);
    }

    /**
     * @return string
     */
    public function getCustomerInvoiceAddress(): string
    {
        return $this->cleanStringProp($this->customerInvoiceAddress);
    }

    /**
     * @return string
     */
    public function getInfoLastUpdate(): string
    {
        return $this->cleanStringProp($this->infoLastUpdate);
    }

    /**
     * @return string
     */
    public function getInfoLastUpdateTime(): string
    {
        return $this->cleanStringProp($this->infoLastUpdateTime);
    }

    /**
     * @return string
     */
    public function getInfoLastUpdateBy(): string
    {
        return $this->cleanStringProp($this->infoLastUpdateBy);
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->isReturnResult() && ! empty($this->getCustomerCode()) && empty($this->getErrorMessage());
    }
}