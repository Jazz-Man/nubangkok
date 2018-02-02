<?php

namespace Encomage\Cms\Setup;

use Magento\Cms\Model\BlockRepository;
use Magento\Cms\Model\BlockFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\InstallDataInterface;

class InstallData implements InstallDataInterface
{
    private $blockFactory;
    private $blockRepository;

    public function __construct(
        BlockFactory $blockFactory,
        BlockRepository $blockRepository
    )
    {
        $this->blockFactory = $blockFactory;
        $this->blockRepository = $blockRepository;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $content = <<<EOD
          <div class="left-menu-big-margin "><a href="#">Inspiration</a></div>
<div><a class="font-less" href="#">Customer care</a></div>
<div><a class="font-less" href="#">Product care</a></div>
<div><a class="font-less" href="#">Contact us</a></div>
<div><a class="font-less" href="#">Career</a></div>
<div class="social-links">
<span class="social-acc-pic"> <img src="{{view url=images/facebook.png}}" alt="facebook-icon" /></span>
<span class="social-acc-pic"> <img src="{{view url=images/instagram.png}}" alt="instagram-icon" /></span> 
<span class="social-acc-pic"> <img src="{{view url=images/youtube.png}}" alt="youtube-icon" /></span> 
<span class="social-acc-pic"> <img src="{{view url=images/empty-link.png}}" alt="youtube-icon" /></span>
</div>
 
EOD;

        $sidebarLinksBlock = [
            'title' => 'Left sidebar cms static block',
            'identifier' => 'left_sidebar_cms_static_block',
            'stores' => ['0'],
            'is_active' => 1,
            'content' => $content
        ];

        $block = $this->blockFactory->create(['data' => $sidebarLinksBlock]);
        $this->blockRepository->save($block);
    }
}