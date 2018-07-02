<?php
namespace Encomage\ErpIntegration\Block\Adminhtml\Form\Field;
use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
class CategoriesCodes extends AbstractFieldArray
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
        $this->addColumn('erp_category_code', [
            'label' => __('ERP Category Code'),
        ]);

        $this->addColumn('category_path', [
            'label' => __('Category Path'),
        ]);

        return $this;
    }
}