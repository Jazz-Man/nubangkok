<?php
namespace Encomage\ErpIntegration\Block\Adminhtml;

class Product extends \Magento\Catalog\Block\Adminhtml\Product
{
    /**
     * @return \Magento\Catalog\Block\Adminhtml\Product
     */
    protected function _prepareLayout()
    {
        $this->buttonList->add(
            'import_manuall',
            ['label' => __('Import Manually'),
                'onclick' => "setLocation('{$this->getUrl('erp/import/manually')}')",
                'class' => 'primary add']
        );
        return parent::_prepareLayout();
    }
}