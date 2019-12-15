<?php


namespace Encomage\ErpIntegration\Model\Api;


/**
 * Class ErpCreateInvoiceResponse
 *
 * @package Encomage\ErpIntegration\Model\Api
 */
class ErpCreateInvoiceResponse extends Filter
{

    /**
     * @var string
     */
    public $DocNo;
    /**
     * @var string
     */
    public $TransDate;
    /**
     * @var float
     */
    public $SalesBalance;
    /**
     * @var float
     */
    public $TaxAmount;
    /**
     * @var float
     */
    public $InvoiceAmount;
    /**
     * @var integer
     */
    public $recId;

    /**
     * @return string
     */
    public function getDocNo(): string
    {
        return $this->cleanStringProp($this->DocNo);
    }

    /**
     * @return string
     */
    public function getTransDate(): string
    {
        return $this->cleanStringProp($this->TransDate);
    }

    /**
     * @return float
     */
    public function getSalesBalance(): float
    {
        return $this->filterNumericProps($this->SalesBalance);
    }

    /**
     * @return int|string
     */
    public function getTaxAmount()
    {
        return $this->filterNumericProps($this->TaxAmount);
    }

    /**
     * @return int|string
     */
    public function getInvoiceAmount()
    {
        return $this->filterNumericProps($this->InvoiceAmount);
    }

    /**
     * @return int|string
     */
    public function getRecId()
    {
        return $this->filterNumericProps($this->recId);
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->isReturnResult() && empty($this->getErrorMessage());
    }
}