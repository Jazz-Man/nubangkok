<?php
/**
 * @author Andrey Bondarenko
 * @link http://encomage.com
 * @mail info@encomage.com
 */

namespace Encomage\StoreLocator\Controller\Adminhtml\Markers;

use Encomage\StoreLocator\Helper\Config;
use Encomage\StoreLocator\Logger\Logger;
use Encomage\StoreLocator\Model\MarkerFactory;
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Delete
 *
 * @package Encomage\StoreLocator\Controller\Adminhtml\Markers
 */
class Delete extends Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;
    /**
     * @var \Encomage\StoreLocator\Model\Marker
     */
    protected $_markerObject;

    /**
     * @var \Encomage\StoreLocator\Helper\Config
     */
    protected $_logger;

    /**
     * Delete constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Encomage\StoreLocator\Model\MarkerFactory $markerFactory
     * @param \Encomage\StoreLocator\Helper\Config $config
     * @param \Encomage\StoreLocator\Logger\Logger $logger
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        MarkerFactory $markerFactory,
        Config $config,
        Logger $logger
    )
    {
        parent::__construct($context);
        $this->_resultPageFactory = $resultPageFactory;
        $this->_markerObject = $markerFactory->create();
        $this->_logger = $logger;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $markerId = (int)$this->getRequest()->getParam('id', 0);
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($markerId) {
            try {
                $this->_markerObject->setEntityId($markerId)->deleteMarker();
                $this->messageManager->addSuccessMessage(__('Store marker has been deleted'));
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage(__('Something went wrong.'));
                $this->_logger->logException($e);
            }
        }
        return $resultRedirect->setPath('*/*/grid');
    }
}