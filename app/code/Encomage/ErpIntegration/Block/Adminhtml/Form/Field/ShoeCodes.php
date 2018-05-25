<?php
namespace Encomage\ErpIntegration\Block\Adminhtml\Form\Field;
use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
class ShoeCodes extends AbstractFieldArray
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
        $this->addColumn('erp_shoe_code', [
            'label' => __('ERP Shoe Code'),
        ]);

        $this->addColumn('shoe_category_value', [
            'label' => __('Shoe Category Value'),
        ]);

        return $this;
    }
}