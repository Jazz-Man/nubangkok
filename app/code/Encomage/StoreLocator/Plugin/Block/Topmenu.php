<?php
/**
 * @author Andrey Bondarenko
 * @link http://encomage.com
 * @mail info@encomage.com
 */

namespace Encomage\StoreLocator\Plugin\Block;


use Encomage\StoreLocator\Helper\Config;
use Magento\Framework\Data\Tree\NodeFactory;
use Magento\Framework\UrlInterface;

/**
 * Class Topmenu
 *
 * @package Encomage\StoreLocator\Plugin\Block
 */
class Topmenu
{
    /**
     * @var \Magento\Framework\Data\Tree\NodeFactory
     */
    protected $_nodeFactory;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var \Encomage\StoreLocator\Helper\Config
     */
    protected $_configHelper;

    /**
     * Topmenu constructor.
     * @param \Magento\Framework\Data\Tree\NodeFactory $nodeFactory
     */
    public function __construct(
        NodeFactory $nodeFactory,
        UrlInterface $urlBuilder,
        Config $configHelper
    )
    {
        $this->_urlBuilder = $urlBuilder;
        $this->_nodeFactory = $nodeFactory;
        $this->_configHelper = $configHelper;
    }

    /**
     * @param \Magento\Theme\Block\Html\Topmenu $subject
     * @param string $outermostClass
     * @param string $childrenWrapClass
     * @param int $limit
     */
    public function beforeGetHtml(
        \Magento\Theme\Block\Html\Topmenu $subject,
        $outermostClass = '',
        $childrenWrapClass = '',
        $limit = 0
    )
    {
        if ($this->_configHelper->isEnabledFrontendPage()) {
            $node = $this->_nodeFactory->create(
                [
                    'data' => [
                        'name' => __('Store List'),
                        'id' => 'store-locator',
                        'url' => $this->_urlBuilder->getUrl('storelocator')
                    ],
                    'idField' => 'id',
                    'tree' => $subject->getMenu()->getTree()
                ]
            );
            $subject->getMenu()->addChild($node);
        }
    }
}