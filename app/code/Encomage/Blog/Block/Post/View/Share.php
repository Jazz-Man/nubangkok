<?php

namespace Encomage\Blog\Block\Post\View;

use Magefan\Blog\Block\Post\AbstractPost;
use Encomage\Theme\Block\Html\Page\FacebookShareLinkInterface;

class Share extends AbstractPost implements FacebookShareLinkInterface
{

    /**
     * @var \Encomage\Theme\Helper\Data
     */
    private $helper;

    /**
     * Share constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magefan\Blog\Model\Post $post
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Cms\Model\Template\FilterProvider $filterProvider
     * @param \Magefan\Blog\Model\PostFactory $postFactory
     * @param \Magefan\Blog\Model\Url $url
     * @param \Encomage\Theme\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magefan\Blog\Model\Post $post,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magefan\Blog\Model\PostFactory $postFactory,
        \Magefan\Blog\Model\Url $url,
        \Encomage\Theme\Helper\Data $helper,
        array $data = []
    )
    {
        parent::__construct($context, $post, $coreRegistry, $filterProvider, $postFactory, $url, $data);
        $this->helper = $helper;
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->helper->getFacebookShareLink($this->getPost()->getPostUrl());
    }

    /**
     * @return string
     */
    public function getCssClass()
    {
        return $this->getData('css_class');
    }

    /**
     * @param string $cssClass
     * @return $this|mixed
     */
    public function setCssClass(string $cssClass)
    {
        $this->setData('css_class', $cssClass);
        return $this;
    }
}