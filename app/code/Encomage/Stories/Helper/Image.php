<?php
namespace Encomage\Stories\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Filesystem;

/**
 * Class Image
 * @package Encomage\Stories\Helper
 */
class Image extends AbstractHelper
{
    /**
     * @var Filesystem\Directory\WriteInterface
     */
    private $mediaDirectory;

    /**
     * Image constructor.
     * @param Context $context
     * @param Filesystem $filesystem
     */
    public function __construct(
        Context $context,
        Filesystem $filesystem
    )
    {
        $this->mediaDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        parent::__construct($context);
    }

    /**
     * @param $image
     * @return bool
     */
    public function deleteStoryImage($image)
    {
        if (!empty($image)) {
            $mediaPath = $this->mediaDirectory->getAbsolutePath();
            $imagePath = $mediaPath . $image;
            if ($this->mediaDirectory->isFile($imagePath)) {
                @unlink($imagePath);
                return true;
            }
        }
        return false;
    }
}