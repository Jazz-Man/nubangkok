<?php

namespace Encomage\ErpIntegration\Block\Adminhtml\Form\Field;

use Encomage\ErpIntegration\Block\Adminhtml\Form\Renderer\SelectOptions;
use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Backend\Block\Template\Context;

/**
 * Class ColorCodes.
 */
class ColorCodes extends AbstractFieldArray
{
    /**
     * @var \Encomage\ErpIntegration\Block\Adminhtml\Form\Renderer\SelectOptions
     */
    private $selectOptions;

    /**
     * @param \Magento\Backend\Block\Template\Context                              $context
     * @param \Encomage\ErpIntegration\Block\Adminhtml\Form\Renderer\SelectOptions $selectOptions
     * @param array                                                                $data
     */
    public function __construct(
        Context $context,
        SelectOptions $selectOptions,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->selectOptions = $selectOptions;
    }


    protected function _prepareToRender()
    {
        $this->addColumn('erp_color_code', ['label' => __('ERP Color Code')]);

        $this->addColumn('erp_color_value', [
            'label' => __('Associated Color'),
            'renderer' => $this->selectOptions,
        ]);

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }
}
