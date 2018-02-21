<?php

namespace Encomage\Careers\Setup;

use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var BlockRepositoryInterface
     */
    private $blockRepository;

    /**
     * UpgradeData constructor.
     * @param BlockRepositoryInterface $blockRepository
     */
    public function __construct(
        BlockRepositoryInterface $blockRepository
    )
    {
        $this->blockRepository = $blockRepository;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '0.0.2', '<')) {
            $this->updateLeftLinksSidebarBlock();
        }
    }

    private function updateLeftLinksSidebarBlock()
    {
        $content = <<<EOD
        <div style="margin-bottom: 1rem;"><a class="violet-link" style="margin-bottom: 1rem;" href='{{store url="why-nu"}}'>WHY <strong style="font-size: 18px;">nu</strong> ?</a></div>
<div style="margin-bottom: 1rem;"><a href="#">INSPIRATION</a></div>
<div style="margin-bottom: 1rem;"><a href="#">nu STORIES (yours and ours)</a></div>
<div style="margin-bottom: 1rem;"><a href='{{store url="customer-care"}}'>CUSTOMER CARE</a></div>
<div style="margin-bottom: 1rem;"><a href='{{store url="product-care"}}'>PRODUCT CARE</a></div>
<div style="margin-bottom: 1rem;"><a href='{{store url="contact-us"}}'>CONTACT US</a></div>
<div><a href='{{store url="careers/listing"}}'>CAREER</a></div>
<div class="social-links" style="margin-bottom: 1rem;">
<a class="social-acc-pic" style="padding-top: 0;" href="https://www.facebook.com" target="_blank"> <img src="{{view url=images/facebook.png}}" alt="facebook-icon" /> </a> 
<a class="social-acc-pic" style="padding-top: 0;" href="https://www.instagram.com" target="_blank"> <img src="{{view url=images/instagram.png}}" alt="instagram-icon" /> </a> 
<a class="social-acc-pic" style="padding-top: 0;" href="https://www.youtube.com" target="_blank"> <img src="{{view url=images/youtube.png}}" alt="youtube-icon" /> </a> 
<a class="social-acc-pic" style="padding-top: 0;"> <img src="{{view url=images/empty-link.png}}" alt="youtube-icon" /></a>
</div>
<div>
<p>#nuBangkok</p>
</div>
<div><a href='{{store url="policies"}}'>POLICIES</a></div>
EOD;
        
        $block = $this->blockRepository->getById('left_sidebar_cms_static_block');
        $block->setContent($content);
        $this->blockRepository->save($block);
    }
}