<?php
/**
 * @author Andrey Bondarenko
 * @link http://encomage.com
 * @mail info@encomage.com
 */

namespace Encomage\StoreLocator\Controller\Adminhtml\Markers;

/**
 * Class InlineEdit
 * @package Encomage\StoreLocator\Controller\Adminhtml\Markers
 */
class InlineEdit extends \Magento\Backend\App\Action
{
    /**
     * @var \Encomage\StoreLocator\Model\Marker
     */
    protected $_markerObject;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $_jsonFactory;

    /**
     * @var \Encomage\StoreLocator\Logger\Logger
     */
    protected $_logger;

    /**
     * InlineEdit constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Encomage\StoreLocator\Model\MarkerFactory $markerFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Encomage\StoreLocator\Logger\Logger $logger
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Encomage\StoreLocator\Model\MarkerFactory $markerFactory,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Encomage\StoreLocator\Logger\Logger $logger
    )
    {
        parent::__construct($context);
        $this->_markerObject = $markerFactory->create();
        $this->_jsonFactory = $jsonFactory;
        $this->_logger = $logger;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->_jsonFactory->create();
        $error = false;
        $messages = [];
        if ($this->getRequest()->getParam('isAjax')) {
            $postMarkers = $this->getRequest()->getParam('items', []);
            if (!count($postMarkers)) {
                $messages[] = __('Incorrect input data');
                $error = true;
            } else {
                foreach (array_keys($postMarkers) as $markerId) {
                    /** @var \Encomage\StoreLocator\Model\Marker $marker */
                    $marker = $this->_markerObject->loadMarkerById((int)$markerId);
                    if (!$marker->getId()) {
                        $messages[] = $this->getError(
                            $this->_markerObject,
                            __('Marker wasn\'t found')
                        );
                        $error = true;
                    } else {
                        try {
                            $marker->setData(
                                array_merge($this->_markerObject->getData(),
                                    $postMarkers[$markerId]
                                )
                            );
                            $marker->saveMarker();
                        } catch (\Exception $e) {
                            $messages[] = $this->getError(
                                $this->_markerObject, __($e->getMessage())
                            );
                            $error = true;
                            $this->_logger->logException($e);
                        }
                    }
                }
            }
        }
        return $resultJson->setData(
            [
                'messages' => $messages,
                'error' => $error
            ]
        );
    }

    /**
     * @param \Encomage\StoreLocator\Model\Marker $marker
     * @param $errorText
     * @return string
     */
    protected function getError(\Encomage\StoreLocator\Model\Marker $marker, $errorText)
    {
        return '[Marker ID: ' . $marker->getId() . '] ' . $errorText;
    }
}
