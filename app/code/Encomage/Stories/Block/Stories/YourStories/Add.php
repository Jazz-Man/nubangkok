<?php
namespace Encomage\Stories\Block\Stories\YourStories;

use Magento\Framework\View\Element\Template;

/**
 * Class Add
 * @package Encomage\Stories\Block\Stories\YourStories
 */
class Add extends Template
{
    /**
     * @return string
     */
    public function getDate()
    {
        $newDate = new \DateTime();
        return $newDate->format('m/d/Y');
    }
}