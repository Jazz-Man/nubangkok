<?php

namespace Encomage\Slider\Controller\Adminhtml\Banner;

class Save extends \Mageplaza\BannerSlider\Controller\Adminhtml\Banner\Save
{
    public function execute()
    {
        $data = $this->getRequest()->getPost('banner');
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $data = $this->filterData($data);
            $banner = $this->initBanner();
            $banner->setData($data);

            $uploadFile = $this->uploadModel->uploadFileAndGetName('upload_file', $this->imageModel->getBaseDir(), $data);
            $mobileUploadFile = $this->uploadModel->uploadFileAndGetName('mobile_upload_file', $this->imageModel->getBaseDir(), $data);

            $banner->setUploadFile($uploadFile);
            $banner->setMobileUploadFile($mobileUploadFile);
            $sliders = $this->getRequest()->getPost('sliders', -1);
            if ($sliders != -1) {
                $banner->setSlidersData($this->jsHelper->decodeGridSerializedInput($sliders));
            }
            $this->_eventManager->dispatch(
                'mageplaza_bannerslider_banner_prepare_save',
                [
                    'banner' => $banner,
                    'request' => $this->getRequest()
                ]
            );
            try {
                $banner->save();
                $this->messageManager->addSuccess(__('The Banner has been saved.'));
                $this->_session->setMageplazaBannersliderBannerData(false);
                if ($this->getRequest()->getParam('back')) {
                    $resultRedirect->setPath(
                        'mageplaza_bannerslider/*/edit',
                        [
                            'banner_id' => $banner->getId(),
                            '_current' => true
                        ]
                    );
                    return $resultRedirect;
                }
                $resultRedirect->setPath('mageplaza_bannerslider/*/');
                return $resultRedirect;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the Banner.'));
            }
            $this->_getSession()->setMageplazaBannersliderBannerData($data);
            $resultRedirect->setPath(
                'mageplaza_bannerslider/*/edit',
                [
                    'banner_id' => $banner->getId(),
                    '_current' => true
                ]
            );
            return $resultRedirect;
        }
        $resultRedirect->setPath('mageplaza_bannerslider/*/');
        return $resultRedirect;
    }
}