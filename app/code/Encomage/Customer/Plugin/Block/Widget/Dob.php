<?php

namespace Encomage\Customer\Plugin\Block\Widget;

use Magento\Framework\Locale\ResolverInterface;
use Magento\Customer\Block\Widget\Dob as Subject;

/**
 * Class Dob
 * @package Encomage\Customer\Plugin\Block\Widget
 */
class Dob
{
    /**
     * @var ResolverInterface
     */
    protected $localeResolver;

    /**
     * Dob constructor.
     * @param ResolverInterface $localeResolver
     */
    public function __construct(
        ResolverInterface $localeResolver
    )
    {
        $this->localeResolver = $localeResolver;
    }

    /**
     * @param Subject $subject
     * @param $result
     * @return string
     */
    public function afterGetDateFormat(Subject $subject, $result)
    {

        if ($this->localeResolver->getLocale() == 'th_TH') {
            return 'dd/mm/Y';
        }

        switch ($result) {
            case 'M/d/Y':
                return 'mm/dd/Y';
            case 'd/M/Y':
                return 'dd/mm/Y';
            default:
                return $result;
        }


    }
}