<?php

namespace NuBangkok\All\Override\Swatches\Block\Adminhtml\Attribute\Edit\Options;

use Magento\Swatches\Block\Adminhtml\Attribute\Edit\Options\Visual as VisualAlias;

/**
 * Class Visual
 *
 * @package NuBangkok\All\Override\Swatches\Block\Adminhtml\Attribute\Edit\Options
 */
class Visual extends VisualAlias
{

    /**
     * @param null $swatchStoreValue
     *
     * @return array|string|void
     */
    protected function reformatSwatchLabels($swatchStoreValue = null)
    {
        if ($swatchStoreValue === null) {
            return;
        }
        $newSwatch = [];
        foreach ($swatchStoreValue as $key => $value) {
            if ($value[0] == '#') {
                $newSwatch[$key] = 'background: '.$value;
            } elseif ($value[0] == '/') {
                $mediaUrl = $this->swatchHelper->getSwatchMediaUrl();
                $newSwatch[$key] = 'background: url('.$mediaUrl.$value.'); background-size: cover;';
            }
        }
        return $newSwatch;
    }

}