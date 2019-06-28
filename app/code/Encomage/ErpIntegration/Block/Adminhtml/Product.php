<?php
namespace Encomage\ErpIntegration\Block\Adminhtml;

class Product extends \Magento\Catalog\Block\Adminhtml\Product
{
    /**
     * @return \Magento\Catalog\Block\Adminhtml\Product
     */
    protected function _prepareLayout()
    {
        /*$page = (int)$this->getRequest()->getParam('p', 0);
        $param = [];
        if ($page) {
            $param['p'] = $page;
        }
        $this->buttonList->add(
            'import_manuall',
            ['label' => __('Import Manually'),
                'onclick' => "setLocation('{$this->getUrl('erp/import/manually', $param)}')",
                'class' => 'primary add']
        );*/
        return parent::_prepareLayout();
    }
}