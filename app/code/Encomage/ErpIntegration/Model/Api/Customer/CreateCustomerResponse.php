<?php


namespace Encomage\ErpIntegration\Model\Api\Customer;


use Encomage\ErpIntegration\Model\Api\Filter;

/**
 * Class CreateCustomerResponse
 *
 * @package Encomage\ErpIntegration\Model\Api\Customer
 */
class CreateCustomerResponse extends Filter
{

    /**
     * @var bool
     */
    private $returnResult = false;
    /**
     * @var string
     */
    private $errorMessage;
    /**
     * @var string
     */
    private $customerCode;
    /**
     * @var string
     */

    private $customerName;
    /**
     * @var string
     */
    private $customerInvoiceAddress;
    /**
     * @var string
     */
    private $infoLastUpdate;
    /**
     * @var string
     */
    private $infoLastUpdateTime;
    /**
     * @var string
     */
    private $infoLastUpdateBy;


    /**
     * @return bool
     */
    public function isReturnResult(): bool
    {
        return $this->returnResult;
    }

    /**
     * @return string
     */
    public function getErrorMessage(): string
    {
        return $this->cleanStringProp($this->errorMessage);
    }

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