<?php

namespace Encomage\Slider\Block\Adminhtml\Banner\Edit\Tab;

class Banner extends \Mageplaza\BetterSlider\Block\Adminhtml\Banner\Edit\Tab\Banner
{
    protected function _prepareForm()
    {
        /** @var \Mageplaza\BetterSlider\Model\Banner $banner */
        $banner = $this->_coreRegistry->registry('mageplaza_betterslider_banner');
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('banner_');
        $form->setFieldNameSuffix('banner');
        $fieldset = $form->addFieldset(
            'base_fieldset',
            [
                'legend' => __('Banner Information'),
                'class'  => 'fieldset-wide'
            ]
        );
        $fieldset->addType('image', 'Mageplaza\BetterSlider\Block\Adminhtml\Banner\Helper\Image');
        if ($banner->getId()) {
            $fieldset->addField(
                'banner_id',
                'hidden',
                ['name' => 'banner_id']
            );
        }
        $fieldset->addField(
            'name',
            'text',
            [
                'name'  => 'name',
                'label' => __('Name'),
                'title' => __('Name'),
                'required' => true,
            ]
        );
        $fieldset->addField(
            'upload_file',
            'image',
            [
                'name'  => 'upload_file',
                'label' => __('Upload File'),
                'title' => __('Upload File'),
            ]
        );

        $fieldset->addField(
            'mobile_upload_file',
            'image',
            [
                'name'  => 'mobile_upload_file',
                'label' => __('Mobile Upload File'),
                'title' => __('Mobile Upload File'),
            ]
        );

        $fieldset->addField(
            'status',
            'select',
            [
                'name'  => 'status',
                'label' => __('Status'),
                'title' => __('Status'),
                'values' => $this->statusOptions->toOptionArray(),
            ]
        );

        $bannerData = $this->_session->getData('mageplaza_betterslider_banner_data', true);
        if ($bannerData) {
            $banner->addData($bannerData);
        } else {
            if (!$banner->getId()) {
                $banner->addData($banner->getDefaultValues());
            }
        }
        $form->addValues($banner->getData());
        $this->setForm($form);
        return $this;
    }

}