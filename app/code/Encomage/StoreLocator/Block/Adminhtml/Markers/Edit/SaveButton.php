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
 * Class SaveButton
 * @package Encomage\StoreLocator\Block\Adminhtml\Markers\Edit
 */
class SaveButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        return [
            'label' => __('Save'),
            'on_click' => sprintf("location.href = '%s';", $this->_getSaveUrl()),
            'class' => 'save primary',
            'sort_order' => 40
        ];
    }

    /**
     * @return string
     */
    protected function _getSaveUrl()
    {
        return $this->getUrl('*/*/save');
    }
}