<?php

namespace Encomage\Cms\Setup;

use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;


class UpgradeData implements UpgradeDataInterface
{
    private $pageRepository;
    private $blockRepository;
    private $blockFactory;
    private $scopeConfig;
    private $configResource;

    public function __construct(
        PageRepositoryInterface $pageRepository,
        BlockRepositoryInterface $blockRepository,
        \Magento\Cms\Model\BlockFactory $blockFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Config\Model\ResourceModel\Config $configResource
    )
    {
        $this->pageRepository = $pageRepository;
        $this->blockRepository = $blockRepository;
        $this->blockFactory = $blockFactory;
        $this->scopeConfig = $scopeConfig;
        $this->configResource = $configResource;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '0.0.2', '<')) {
            $this->createBlockForRightImageOnRegisterForm();
        }

        if (version_compare($context->getVersion(), '0.0.3', '<')) {
            $this->modifyFooterCopyright();
        }

        if (version_compare($context->getVersion(), '0.0.4', '<')) {
            $this->updateTitleAndPageLayoutForHomePage();
        }

        if (version_compare($context->getVersion(), '0.0.5', '<')) {
            $this->updateLeftLinksSidebarBlock();
        }

        if (version_compare($context->getVersion(), '0.0.6', '<')) {
            $this->comingSoonCategoryBlockWomenClothing();
        }
    }

    private function createBlockForRightImageOnRegisterForm()
    {
        $block = $this->blockFactory->create();
        $block->addData(
            [
                'title' => 'Register Form right',
                'identifier' => 'register-form-right',
                'stores' => [0],
                'is_active' => 1,
                'content' => '<div class="register-form-right-image"><img src="{{view url=images/cms/sign-in.jpg}}" alt="sign-in"></div>'
            ]
        );
        $this->blockRepository->save($block);
        return $this;
    }

    private function updateTitleAndPageLayoutForHomePage()
    {
        $homePage = $this->pageRepository->getById('home');
        $homePage->addData(
            [
                'title' => 'nuBangkok Shop Shoes, Bags, Apparels Goods Handmade in Thailand',
                'page_layout' => '2columns-left'
            ]
        );
        $this->pageRepository->save($homePage);
        return $this;
    }

    private function modifyFooterCopyright()
    {
        $this->configResource->saveConfig('design/footer/copyright',
            'nu Bangkok Copyright ©2018. All Rights Reserved',
            'default',
            0
        );

        $this->configResource->saveConfig('design/footer/copyright',
            'nu Bangkok Copyright ©2018. All Rights Reserved',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORES,
            1
        );
        return $this;
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
<div><a href="#">CAREER</a></div>
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

    private function comingSoonCategoryBlockWomenClothing()
    {
        $content = <<<EOD
<p><img src="{{view url=images/cms/05Women-clothes.jpg}}" width="1000" height="288" /></p>
<p><strong><span style="font-size: x-large; margin-bottom: 2rem;">OUR WOMEN CLOTHING IS COMING LATER!</span></strong>
</p>
<p></p>
<p style="margin-bottom: 2rem;">Stay tuned!</p>
<p style="margin-bottom: 2rem;">If you would like to be notified when it launches please enter your email below!</p>
EOD;
        $block = $this->blockFactory->create();
        $block->addData(
            [
                'title' => 'Coming Soon Category - Women Clothing',
                'identifier' => 'coming_coon_category_women_clothing',
                'stores' => [0],
                'is_active' => 1,
                'content' => $content
            ]
        );
        $this->blockRepository->save($block);
        return $this;
    }
}
