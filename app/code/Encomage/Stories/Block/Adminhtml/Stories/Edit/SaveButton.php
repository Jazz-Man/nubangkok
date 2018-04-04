<?php
namespace Encomage\Stories\Block\Adminhtml\Stories\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class SaveButton
 * @package Encomage\Stories\Block\Adminhtml\Stories\Edit
 */
class SaveButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        return [
            'label' => __('Save Story'),
            'class' => 'save primary',
            'data_attribute' => [
                'mage-init' => ['button' => ['event' => 'save', 'target' => '#edit_form']],
                'form-role' => 'save',
            ],
            'sort_order' => 90,
        ];
    }
}