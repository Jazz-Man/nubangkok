<?php
namespace Encomage\Careers\Setup;

use Magento\Cms\Model\BlockRepository;
use Magento\Cms\Model\BlockFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Class InstallData
 * @package Encomage\Careers\Setup
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var BlockRepositoryInterface
     */
    private $blockRepository;
    /**
     * @var BlockInterfaceFactory
     */
    private $blockFactory;

    /**
     * InstallData constructor.
     * @param BlockRepository $blockRepository
     * @param BlockFactory $blockFactory
     */
    public function __construct(
        BlockRepository $blockRepository,
        BlockFactory $blockFactory
    )
    {
        $this->blockRepository = $blockRepository;
        $this->blockFactory = $blockFactory;
    }

    /**
     * Installs data for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->careersImageCmsBlock();
        $this->careersVideoCmsBlock();
    }

    /**
     * Create a CMS block
     */
    public function careersImageCmsBlock()
    {
        $cmsBlock = $this->blockFactory->create();
        $cmsBlock->setIdentifier('career-images-listing-page')
            ->setTitle('Careers images from listing page')
            ->setIsActive(true)
            ->setContent('<p>Don\'t bother making the facilities quite so clean. Save time and hassle and let the display get a little messy. Don\'t worry so much about one particular customer, because you\'re busy and hiring more people takes time and money.</p>
<p><img src="{{media url="wysiwyg/career1.JPG"}}" width="90%" /></p>
<div style="width: 90%;">
<p style="width: 49%; display: inline-block;"><img src="{{media url="wysiwyg/career2.JPG"}}" /></p>
<p style="width: 49%; display: inline-block; float: right;"><img src="{{media url="wysiwyg/career3.JPG"}}" /></p>
<div>
<p>Don\'t bother making the facilities quite so clean. Save time and hassle and let the display get a little messy.</p>
</div>
</div>')
            ->setData('stores', [0]);
        $this->blockRepository->save($cmsBlock);
    }

    /**
     * Create a CMS block
     */
    public function careersVideoCmsBlock()
    {
        $cmsBlock = $this->blockFactory->create();
        $cmsBlock->setIdentifier('career-image-video-listing-page')
            ->setTitle('Careers image/video from listing page')
            ->setIsActive(true)
            ->setContent('<p><video width="90%" height="240" controls="controls" preload="auto" src="{{media url="wysiwyg/SampleVideo_1280x720_1mb.mp4"}}"></video></p>')
            ->setData('stores', [0]);
        $this->blockRepository->save($cmsBlock);
    }
}