<?php

namespace Encomage\Careers\Model\Careers\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Status implements OptionSourceInterface
{
    const STATUS_ENABLED = 1;

    const STATUS_DISABLED = 0;

    protected $options;

    /**#@-*/

    /**
     * Retrieve Visible Status Ids
     *
     * @return int[]
     */
    public function getVisibleStatusIds()
    {
        return [self::STATUS_ENABLED];
    }
    
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options !== null) {
            return $this->options;
        }
        $options[] = [
            'label' => 'Yes',
            'value' => self::STATUS_ENABLED,
        ];
        $options[] = [
            'label' => 'No',
            'value' => self::STATUS_DISABLED,
        ];
        $this->options = $options;
        return $this->options;
    }
}
