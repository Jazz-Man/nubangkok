<?php
/**
 * @author Andrey Bondarenko
 * @link http://encomage.com
 * @mail info@encomage.com
 */

namespace Encomage\StoreLocator\Plugin\View\Page\Config\Generator;

//TODO:: NEED FIND BETTER SOLUTIONS
use Encomage\StoreLocator\Helper\Config;
use Magento\Framework\App\Request\Http;

/**
 * Class Head
 * @package Encomage\StoreLocator\Plugin\View\Page\Config\Generator
 */
class Head
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_request;
    /**
     * @var \Encomage\StoreLocator\Helper\Config
     */
    protected $_helperConfig;

    /**
     * Head constructor.
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Encomage\StoreLocator\Helper\Config $helperConfig
     */
    public function __construct(
        Http $request,
        Config $helperConfig)
    {
        $this->_request = $request;
        $this->_helperConfig = $helperConfig;
    }

    /**
     * @param string $readerContext
     * @param string $generatorContext
     */
    public function beforeProcess(
        $readerContext = '',
        $generatorContext = ''
    )
    {
        if ($this->isAddGoogleApiJs()) {
            $generatorContext->getPageConfigStructure()
                ->addAssets(
                    $this->_helperConfig->getGoogleMapApiJsUrl(),
                    [
                        'src' => $this->_helperConfig->getGoogleMapApiJsUrl(),
                        'src_type' => 'url',
                        'content_type' => 'js'
                    ]
                );
        }
    }

    /**
     * @return bool
     */
    protected function isAddGoogleApiJs()
    {
        if ($this->_request->getModuleName() == 'storelocator' && $this->_request->getControllerName() == 'markers') {
            return ($this->_request->getActionName() == 'create'
                || $this->_request->getActionName() == 'edit');
        }
        return false;
    }
}