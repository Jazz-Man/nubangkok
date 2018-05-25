<?php
namespace Encomage\ErpIntegration\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

class ColorCodes extends AbstractFieldArray
{
    /**
     * Class construct
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add code');
    }

    /**
     * @return $this
     */
    protected function _prepareToRender()
    {
        $this->addColumn('erp_color_code', [
            'label' => __('ERP Color Code'),
        ]);

        $this->addColumn('color_name', [
            'label' => __('Color Name'),
        ]);

        return $this;
    }
}