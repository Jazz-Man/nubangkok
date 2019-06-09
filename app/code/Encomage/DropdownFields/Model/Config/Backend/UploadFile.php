<?php

namespace Encomage\DropdownFields\Model\Config\Backend;

use Magento\Config\Model\Config\Backend\File;

/**
 * Class UploadFile
 * @package Encomage\DropdownFields\Model\Config\Backend
 */
class UploadFile extends File
{
    /**
     * @return string[]
     */
    public function getAllowedExtensions()
    {
        return ['csv', 'xls'];
    }
}