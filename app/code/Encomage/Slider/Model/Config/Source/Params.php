<?php

namespace Encomage\Slider\Model\Config\Source;
use Magento\Framework\Data\OptionSourceInterface;
use Mageplaza\BannerSlider\Model\ResourceModel\Slider\CollectionFactory as SliderCollection;

/**
 * Class Params
 *
 * @package Encomage\Slider\Model\Config\Source
 */
class Params implements OptionSourceInterface
{
    /**
     * @var SliderCollection
     */
    private $_sliderCollection ;

    /**
     * Params constructor.
     * @param SliderCollection $sliderCollection
     */
    public function __construct(SliderCollection $sliderCollection)
    {
        $this->_sliderCollection =$sliderCollection;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options =[];
        $collection = $this->_sliderCollection->create();
        $collection->addFieldToSelect(['name']);
        foreach ($collection as $item ) {
            $options[] = [
                'value' => $item->getId(),
                'label' => $item->getName(),
            ];
        }
        return $options;
    }
}