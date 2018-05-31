<?php
namespace Encomage\ErpIntegration\Block\Adminhtml\Form\Field;
use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
class BagsCodes extends AbstractFieldArray
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
        $this->addColumn('erp_bags_code', [
            'label' => __('ERP Bags Code'),
        ]);

        $this->addColumn('bags_category_value', [
            'label' => __('Bags Category Value'),
        ]);

        return $this;
    }
}