<?php

namespace Encomage\Theme\Block\Html\Link;

use Encomage\Theme\Helper\HtmlAttributes;
use Magento\Framework\App\DefaultPathInterface;
use Magento\Framework\View\Element\Html\Link\Current as CurrentAlias;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class Current.
 *
 * @method string       getLabel()
 * @method string       getPath()
 * @method string|null  getCurrentCssClass()
 * @method string       getTitle()
 * @method array|null   getLinkAttributes()
 * @method array|null   getItemAttributes()
 * @method bool|null    getCurrent()
 * @method bool|null    getIsHighlighted()
 * @method CurrentAlias setCurrent(bool $value)
 */
class Current extends CurrentAlias
{
    /**
     * @var \Encomage\Theme\Helper\HtmlAttributes
     */
    private $htmlAttributes;

    /**
     * Current constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\App\DefaultPathInterface      $defaultPath
     * @param \Encomage\Theme\Helper\HtmlAttributes            $htmlAttributes
     * @param array                                            $data
     */
    public function __construct(
        Context $context,
        DefaultPathInterface $defaultPath,
        HtmlAttributes $htmlAttributes,
        array $data = []
    ) {
        $this->htmlAttributes = $htmlAttributes;
        parent::__construct($context, $defaultPath, $data);
    }

    /**
     * Render block HTML.
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (false != $this->getTemplate()) {
            return parent::_toHtml();
        }

        $itemAttributes = [
            'class' => [
                'nav',
                'item',
            ],
        ];

        $currentClass = $this->getCurrentCssClass() ?: 'current';

        if ($this->isCurrent()) {
            $itemAttributes['class'][] = $currentClass;

            $html = "<li {$this->htmlAttributes->getAttributesHtml($this->getItemAttributes(), $itemAttributes)}>";
            $html .= "<strong>{$this->escapeHtml(__($this->getLabel()))}</strong>";
            $html .= '</li>';
        } else {
            $linkAttributes = [
                'href' => $this->getHref(),
                'title' => $this->getTitle() ? __($this->getTitle()) : '',
            ];

            $itemAttributes['class'][] = $this->getIsHighlighted() ? $currentClass : '';

            $html = "<li {$this->htmlAttributes->getAttributesHtml($this->getItemAttributes(), $itemAttributes)}>";

            $html .= "<a {$this->htmlAttributes->getAttributesHtml($this->getLinkAttributes(), $linkAttributes)}>";

            if ($this->getIsHighlighted()) {
                $html .= '<strong>';
            }

            $html .= $this->escapeHtml(__($this->getLabel()));

            if ($this->getIsHighlighted()) {
                $html .= '</strong>';
            }

            $html .= '</a></li>';
        }

        return $html;
    }

}
