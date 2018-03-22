<?php

namespace Encomage\Stories\Block\Stories\YourStories;
;

use Encomage\Theme\Block\Html\Page\FacebookShareLinkInterface;
use Magento\Framework\View\Element\Template;
use Encomage\Theme\Helper\Data as ShareHelper;


class Share extends Template implements FacebookShareLinkInterface
{
    private $helper;

    /**
     * Share constructor.
     * @param Template\Context $context
     * @param ShareHelper $shareHelper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        ShareHelper $shareHelper,
        array $data
    )
    {
        parent::__construct($context, $data);
        $this->helper = $shareHelper;
    }

    /**
     * Class constructor
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('Magento_Theme::html/page/share.phtml');
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->helper->getFacebookShareLink('#');
    }

    /**
     * @return mixed
     */
    public function getCssClass()
    {
        return $this->getData('css_class');
    }

    /**
     * @param string $cssClass
     * @return $this
     */
    public function setCssClass(string $cssClass)
    {
        $this->setData('css_class', $cssClass);
        return $this;
    }
}