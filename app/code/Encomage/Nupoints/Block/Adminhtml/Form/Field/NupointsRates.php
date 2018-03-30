<?php
namespace Encomage\Nupoints\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

class NupointsRates extends AbstractFieldArray
{
    /**
     * Class construct
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add rate');
    }

    /**
     * @return $this
     */
    protected function _prepareToRender()
    {
        $this->addColumn('nupoints_from', [
            'label' => __('NuPoints From'),
        ]);

        $this->addColumn('nupoints_to', [
            'label' => __('NuPoints To'),
        ]);

        $this->addColumn('money', [
            'label' => __('Money (฿)')
        ]);

        $this->addColumn('related_product', [
            'label' => __('Related Product (SKU)')
        ]);
        return $this;
    }
}