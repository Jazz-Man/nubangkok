<?php

namespace Encomage\Swatches\Model\ResourceModel;

/**
 * Class Swatch
 * @package Encomage\Swatches\Model\ResourceModel
 */
class Swatch extends \Magento\Swatches\Model\ResourceModel\Swatch
{
    /**
     * @param $optionIDs
     * @param null $type
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function clearSwatchOptionByOptionIdAndType($optionIDs, $type = null)
    {
        if (count($optionIDs)) {
            foreach ($optionIDs as $optionId) {
                $where = ['option_id' => $optionId];
                if ($type !== null) {
                    $where['type = ?'] = $type;
                }
                $this->getConnection()->delete($this->getMainTable(), $where);
            }
        }
    }
}