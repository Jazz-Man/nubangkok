<?php


namespace Encomage\ErpIntegration\Model\Api;


use Encomage\ErpIntegration\Helper\StringUtils;
use stdClass;
use function is_float;
use function is_int;

/**
 * Class Filter
 *
 * @package Encomage\ErpIntegration\Model\Api
 */
abstract class Filter
{

    /**
     * @var bool
     */
    public $returnResult = false;

    /**
     * @var string
     */
    public $errorMessage;

    /**
     * @var \Encomage\ErpIntegration\Helper\StringUtils
     */
    public $string;

    /**
     * Filter constructor.
     *
     * @param \stdClass $obj
     */
    public function __construct(stdClass $obj)
    {
        $this->string = new StringUtils();

        foreach ($obj as $property => $value) {
            if (property_exists($this, $property)) {
                $this->$property = $value;
            }
        }
    }

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
     * @return bool
     */
    abstract public function isValid(): bool ;

    /**
     * @param string|float|int $prop
     *
     * @return string|int
     */
    protected function filterNumericProps($prop)
    {
        $prop = $this->string->cleanString($prop);
        $prop = $this->string->trim($prop);

        if (is_float($prop)) {
            $filter = FILTER_SANITIZE_NUMBER_FLOAT;
        } elseif (is_int($prop)) {
            $filter = FILTER_SANITIZE_NUMBER_INT;
        } else {
            $filter = FILTER_DEFAULT;
        }

        return filter_var($prop, $filter);
    }

    /**
     * @param mixed $prop
     *
     * @return string
     */
    public function cleanStringProp($prop): string
    {
        $prop = $this->string->cleanString($prop);
        $prop = $this->string->trim($prop);
        $prop = filter_var($prop, FILTER_SANITIZE_STRING);

        return $prop;
    }

}