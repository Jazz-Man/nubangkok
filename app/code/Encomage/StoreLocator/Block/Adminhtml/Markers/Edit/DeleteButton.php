<?php
/**
 * @author Andrey Bondarenko
 * @link http://encomage.com
 * @mail info@encomage.com
 */

namespace Encomage\StoreLocator\Block\Adminhtml\Markers\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Cms\Block\Adminhtml\Page\Edit\GenericButton;

/**
 * Class DeleteButton
 * @package Encomage\StoreLocator\Block\Adminhtml\Markers\Edit
 */
class DeleteButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        $buttonData = [];
        if ($this->_getMarkerId()) {
            $buttonData = [
                'label' => __('Delete'),
                'on_click' => sprintf("location.href = '%s';", $this->_getDeleteUrl()),
                'class' => 'delete',
                'sort_order' => 20
            ];
        }
        return $buttonData;
    }

    /**
     * @return string
     */
    protected function _getDeleteUrl()
    {
        return $this->getUrl('*/*/delete', ['id' => $this->_getMarkerId()]);
    }

    /**
     * @return int
     */
    protected function _getMarkerId()
    {
        return (int)$this->context->getRequest()->getParam('id');
    }
}