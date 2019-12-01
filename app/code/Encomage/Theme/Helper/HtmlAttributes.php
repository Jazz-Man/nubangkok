<?php

namespace Encomage\Theme\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\View\Element\Context as ViewContext;

/**
 * Class HtmlAttributes.
 */
class HtmlAttributes extends AbstractHelper
{
    /**
     * @var \Magento\Framework\Escaper
     */
    private $_escaper;

    /**
     * HtmlAttributes constructor.
     *
     * @param \Magento\Framework\App\Helper\Context   $context
     * @param \Magento\Framework\View\Element\Context $viewcontext
     */
    public function __construct(
        Context $context,
        ViewContext $viewcontext
    ) {
        $this->_escaper = $viewcontext->getEscaper();
        parent::__construct($context);
    }

    /**
     * @param array|null $attributes
     * @param array      $custom_atts
     *
     * @return string
     */
    public function getAttributesHtml($attributes = null, array $custom_atts = []): string
    {
        $attributesHtml = '';

        if (!empty($attributes)) {
            if (!empty($custom_atts)) {
                $attributes = array_merge_recursive($attributes, $custom_atts);
            }

            foreach ($attributes as $key => $value) {
                if (empty($value)) {
                    continue;
                }

                if (\is_array($value)) {
                    $value = implode(' ', array_filter($value));
                }
                if ('class' === $key && '' === $value) {
                    continue;
                }

                $attributesHtml .= ' '.$this->_escaper->escapeHtml($key).'="'.$this->_escaper->escapeHtml($value).'"';
            }
        }

        return $attributesHtml;
    }
}
