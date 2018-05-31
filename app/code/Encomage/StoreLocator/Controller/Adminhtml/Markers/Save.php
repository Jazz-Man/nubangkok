<?php
/**
 * @author Andrey Bondarenko
 * @link http://encomage.com
 * @mail info@encomage.com
 */

namespace Encomage\StoreLocator\Controller\Adminhtml\Markers;

/**
 * Class Grid
 * @package Encomage\StoreLocator\Controller\Adminhtml\Markers
 */
class Save extends \Magento\Backend\App\Action
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
     * @var \Encomage\StoreLocator\Logger\Logger
     */
    protected $_logger;

    /**
     * Save constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Encomage\StoreLocator\Model\MarkerFactory $markerFactory
     * @param \Encomage\StoreLocator\Logger\Logger $logger
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Encomage\StoreLocator\Model\MarkerFactory $markerFactory,
        \Encomage\StoreLocator\Logger\Logger $logger
    )
    {
        parent::__construct($context);
        $this->_resultPageFactory = $resultPageFactory;
        $this->_markerObject = $markerFactory->create();
        $this->_logger = $logger;
    }

    /**
     * @return $this
     */
    public function execute()
    {
        $entityId = (int)$this->getRequest()->getParam('entity_id');
        $backAction = $this->getRequest()->getParam('back', null);
        $requestParams = $this->getRequest()->getPostValue();
        $responseParams = [];
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if (isset($requestParams['coordinates'])) {
            $coordinates = explode(':', $requestParams['coordinates']);
            if (isset($coordinates[0])) {
                $requestParams['latitude'] = $coordinates[0];
            }
            if (isset($coordinates[1])) {
                $requestParams['longitude'] = $coordinates[1];
            }
        }
        try {
            $errorInRequestData = $this->_markerObject->validateData($requestParams);
            if (!empty($errorInRequestData)) {
                foreach ($errorInRequestData as $item) {
                    $this->messageManager->addErrorMessage($item);
                }
                return $resultRedirect->setPath('*/*/grid');
            }
            if ($entityId) {
                $this->_markerObject->loadMarkerById($entityId);
            }
            $this->_markerObject
                ->addData(
                    [
                        'name' => $requestParams['name'],
                        'latitude' => $requestParams['latitude'],
                        'longitude' => $requestParams['longitude'],
                        'store_id' => implode(',', $requestParams['store_id']),
                        'comment' => $this->getRequest()->getParam('comment', null)
                    ]
                );
            $this->_markerObject->saveMarker();
            $this->messageManager->addSuccessMessage(__('Marker has been saved'));
            $responseParams['id'] = (int)$this->_markerObject->getId();
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Something went wrong while saving the marker.'));
            $this->_logger->logException($e);
        }
        return ($backAction)
            ? $resultRedirect->setPath('*/*/' . $backAction, $responseParams)
            : $resultRedirect->setPath('*/*/grid');
    }
}
