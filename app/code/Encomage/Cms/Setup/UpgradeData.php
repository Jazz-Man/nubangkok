<?php

namespace Encomage\Cms\Setup;

use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Cms\Model\BlockFactory;
use Magento\Cms\Model\PageFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;


class UpgradeData implements UpgradeDataInterface
{
    private $pageRepository;
    private $blockRepository;
    private $pageFactory;
    private $blockFactory;
    private $scopeConfig;
    private $configResource;

    public function __construct(
        PageRepositoryInterface $pageRepository,
        BlockRepositoryInterface $blockRepository,
        BlockFactory $blockFactory,
        PageFactory $pageFactory,
        ScopeConfigInterface $scopeConfig,
        Config $configResource
    )
    {
        $this->pageRepository = $pageRepository;
        $this->blockRepository = $blockRepository;
        $this->blockFactory = $blockFactory;
        $this->pageFactory = $pageFactory;
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

        if (version_compare($context->getVersion(), '0.0.7', '<')) {
            $this->addCmsPagesWhyNuWhereToBuyTerms();
        }

        if (version_compare($context->getVersion(), '0.0.8', '<')) {
            $this->addCmsPagesContactCustomerCareFaqPoliciesProductCare();
        }

        if (version_compare($context->getVersion(), '0.0.9', '<')) {
            $this->modify404pageRemoveUnusedPages();
        }

        if (version_compare($context->getVersion(), '0.1.0', '<')) {
            $this->updateLeftLinksSidebarBlock010();
        }

        if (version_compare($context->getVersion(), '0.1.1', '<')) {
            $this->updateContactUs();
        }
        
        if (version_compare($context->getVersion(), '0.1.2', '<')) {
            $this->updateLeftLinksSidebarBlock012();
        }
        
        if (version_compare($context->getVersion(), '0.1.2', '<')) {
            //$this->upgradeCmsPagesContactCustomerCareFaqPoliciesProductCare();
        }

        if (version_compare($context->getVersion(), '0.1.3', '<')) {
            $this->upgradeCmsPages();
            $this->upgradeLeftSidebarCmsBlock();
        }

        if (version_compare($context->getVersion(), '0.1.4', '<')) {
            $this->upgradeComingSoon014();
            $this->upgradeLeftMenu014();
        }

        if (version_compare($context->getVersion(), '0.1.3', '<')) {
            $this->addCmsBlockOurStories();
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

    private function updateLeftLinksSidebarBlock010()
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
<a class="social-acc-pic" style="padding-top: 0;" href="https://www.facebook.com" target="_blank"> <img src="{{view url=images/icons/homePage/Facebook.svg}}" alt="facebook-icon" /> </a> 
<a class="social-acc-pic" style="padding-top: 0;" href="https://www.instagram.com" target="_blank"> <img src="{{view url=images/icons/homePage/Instagram.svg}}" alt="instagram-icon" /> </a> 
<a class="social-acc-pic" style="padding-top: 0;" href="https://www.youtube.com" target="_blank"> <img src="{{view url=images/icons/homePage/youtube.svg}}" alt="youtube-icon" /> </a> 
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
        <div class="cms-coming-soon-category">
<p><img src="{{media url="wysiwyg/cms/05Women-clothes.jpg"}}" width="1000" height="288" /></p>
<p><strong><span style="font-size: x-large; margin-bottom: 2rem;">OUR WOMEN CLOTHING IS COMING LATER!</span></strong>
</p>
<p></p>
<p style="margin-bottom: 2rem;">Stay tuned!</p>
<p style="margin-bottom: 2rem;">If you would like to be notified when it launches please enter your email below!</p>
</div>
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

    private function addCmsPagesWhyNuWhereToBuyTerms()
    {
        $pages = [
            'why-nu' => [
                'title' => 'Why NU',
                'identifier' => 'why-nu',
                'active' => true,
                'page_layout' => '2columns-left',
                'stores' => [0],
                'content' => <<<EOD
<div class="cms-why-nu-page">
<p>OUR SHOES</p>
<table style="margin-bottom: 40px;" border="0">
<tbody>
<tr>
<td style="display: inline-block; padding-right: 25px;"><img src='{{media url="wysiwyg/cms/grip-icon.png"}}' width="35" /></td>
<td style="display: inline-block; padding-right: 50px; padding-top: 15px;">Grip</td>
<td style="display: inline-block;"></td>
<td style="display: inline-block; padding-right: 25px;"><img src='{{media url="wysiwyg/cms/grip-icon.png"}}' width="35" /></td>
<td style="display: inline-block; padding-top: 15px;">Grip</td>
</tr>
<tr>
<td style="display: inline-block; padding-right: 25px;"><img src='{{media url="wysiwyg/cms/grip-icon.png"}}' width="35" /></td>
<td style="display: inline-block; padding-right: 50px; padding-top: 15px;">Grip</td>
<td style="display: inline-block;"></td>
<td style="display: inline-block; padding-right: 25px;"><img src='{{media url="wysiwyg/cms/grip-icon.png"}}' width="35" /></td>
<td style="display: inline-block; padding-top: 15px;">Grip</td>
</tr>
<tr>
<td style="display: inline-block; padding-right: 25px;"><img src='{{media url="wysiwyg/cms/grip-icon.png"}}' width="35" /></td>
<td style="display: inline-block; padding-right: 50px; padding-top: 15px;">Grip</td>
<td style="display: inline-block;"></td>
<td style="display: inline-block; padding-right: 25px;"><img src='{{media url="wysiwyg/cms/grip-icon.png"}}' width="35" /></td>
<td style="display: inline-block; padding-top: 15px;">Grip</td>
</tr>
</tbody>
</table>
<p><img style="max-width: 90%;" src='{{media url="wysiwyg/cms/why-nu1.jpg"}}' width="1500" height="999" /></p>
<p>In the interests of hygiene we do not accept returns of, or refunds on earrings unless it is defective or a wrong item sent.</p>
<p><img style="max-width: 90%; margin-top: 40px;" src='{{media url="wysiwyg/cms/why-nu2.jpg"}}' width="1500" height="999" /></p>
<p>In the interests of hygiene we do not accept returns of, or refunds on earrings unless it is defective or a wrong item sent.</p>
<p></p>
<p style="margin: 20px 0;">WE GUARANTEE</p>
<p></p>
<div style="margin-bottom: 10px;">
<div class="numberCircle" style="display: inline-block;">1</div>
<p style="display: inline-block; padding-left: 10px; width: 80%;">In the interests of hygiene we do not accept returns of.</p>
</div>
<div style="margin-bottom: 10px;">
<div class="numberCircle" style="display: inline-block;">2</div>
<p style="display: inline-block; padding-left: 10px; width: 80%;">A refunds on earrings unless it is defective or a wrong item sent.</p>
</div>
<div style="margin-bottom: 10px;">
<div class="numberCircle" style="display: inline-block;">3</div>
<p style="display: inline-block; padding-left: 10px; width: 80%;">A refunds on earrings unless it is defective or a wrong item sent.</p>
</div>
</div>
EOD
            ],
            'where-to-buy' => [
                'title' => 'Where to buy',
                'identifier' => 'where-to-buy',
                'active' => true,
                'page_layout' => '2columns-left',
                'stores' => [0],
                'content' => <<<EOD
<div class="cms-where-to-buy-page">
<div class="desktop-change-position-left">
<h1 class="cms-heading" style="margin-bottom: 30px;">WHERE TO BUY</h1>
<div class="accordion-container" style="border-top: 1px solid #55463e;">
<div class="accordion active js-revert-image-on-click" data-gmp-btn="false" data-target="#myImage" data-src="{{media url="wysiwyg/cms/outlet-mini-shops.jpg"}}">Outlet mini shops</div>
</div>
<div class="accordion-container">
<div class="accordion js-revert-image-on-click" data-gmp-btn="true" data-target="#myImage" data-src="{{media url="wysiwyg/cms/nu-roadshow3.jpg"}}">Thailand Malls</div>
</div>
<div class="accordion-container">
<div class="accordion js-revert-image-on-click" data-gmp-btn="false" data-target="#myImage" data-src="{{media url="wysiwyg/cms/bangkok-map.png"}}">Roadshows &amp; Evenets</div>
</div>
</div>
<div class="desktop-change-position-right"><img id="myImage" src="{{media url="wysiwyg/cms/outlet-mini-shops.jpg"}}" alt="" />
<p class="js-show-gm-btn" style="text-align: center; display: none;"><a href="https://goo.gl/gk4eqW" target="_blank" class="cms-button">Open with google map</a></p>
</div>
</div>
EOD

            ],

            'terms-of-services' => [
                'title' => 'Terms of services',
                'identifier' => 'terms-of-service',
                'active' => true,
                'page_layout' => '2columns-left',
                'stores' => [0],
                'content' => <<<EOD
<div class="cms-terms-of-service-page">
<h1 class="cms-heading">TERMS OF SERVICE</h1>
<hr class="cms-heading-line" /><ol>
<li><span style="font-size: small;">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</span></li>
<li><span style="font-size: small;">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</span></li>
<li><span style="font-size: small;">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</span></li>
<li><span style="font-size: small;">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</span></li>
<li><span style="font-size: small;">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</span></li>
<li><span style="font-size: small;">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</span></li>
<li><span style="font-size: small;">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</span></li>
</ol>
</div>
EOD

            ]
        ];

        foreach ($pages as $pageData) {
            $pageObject = $this->pageFactory->create();
            $pageObject->addData($pageData);
            $this->pageRepository->save($pageObject);
        }
        return $this;
    }

    private function addCmsPagesContactCustomerCareFaqPoliciesProductCare()
    {
        $pages = [
            'contact-us' => [
                'title' => 'Contact Us',
                'identifier' => 'contact-us',
                'active' => true,
                'page_layout' => '2columns-left',
                'stores' => [0],
                'content' => <<<EOD
<div class="cms-contact-us">
<h3 class="cms-subheading">Need help?</h3>
<p>Check our <a style="text-decoration: underline;" href="{{store url='faq'}}">Frequently Asked Questions</a> section for help in finding an immediate answer to our most commonly asked questions.</p>
<p></p>
<p></p>
<p></p>
<h3 class="cms-subheading">Find shop?</h3>
<div style="overflow-x: auto;">
<table style="width: 600px;" border="0">
<tbody>
<tr>
<td style="padding-left: 0; width: 25%;" rowspan="6">OUTLET SHOPS</td>
<td>Ari (outside gate 3/4)</td>
<td>062-006-9553</td>
</tr>
<tr>
<td>Siam (inside exit 5)</td>
<td>094-547-9771</td>
</tr>
<tr>
<td>Ching Nonsi</td>
<td>099-454-8646</td>
</tr>
<tr>
<td>Chit Lom</td>
<td>094-547-9778</td>
</tr>
<tr>
<td>Nana</td>
<td>099-192-0019</td>
</tr>
<tr>
<td>Asok</td>
<td>094-454-9776</td>
</tr>
<tr>
<td rowspan="5"></td>
<td>Thong Lo</td>
<td>094-547-9775</td>
</tr>
<tr>
<td>Ekkamai</td>
<td>094-697-5160</td>
</tr>
<tr>
<td>Phra Khanong</td>
<td>099-391-7884</td>
</tr>
<tr>
<td>Udomsuk</td>
<td>092-050-0577</td>
</tr>
<tr>
<td>Bearing</td>
<td>094-649-9006</td>
</tr>
<tr>
<td colspan="3"><img src="{{media url="wysiwyg/cms/central-plaza.png"}}" width="150" /><img style="padding-left: 20px;" src="{{media url="wysiwyg/cms/central-festival.jpeg"}}" width="150" /></td>
</tr>
<tr>
<td rowspan="4"></td>
<td>Ubon Ratchathani</td>
<td>095-046-0492</td>
</tr>
<tr>
<td>Hat Yai</td>
<td>062-053-3811</td>
</tr>
<tr>
<td>Phitsanulok</td>
<td>061-995-8864</td>
</tr>
<tr>
<td>Chiang Rai</td>
<td>094-605-0530</td>
</tr>
</tbody>
</table>
</div>
<p></p>
<h3 class="cms-subheading">Customer Service</h3>
<div style="overflow-x: auto;">
<table border="0">
<tbody>
<tr>
<td style="text-align: right;"><img src="{{media url="wysiwyg/cms/phone-icon.png"}}" alt="" width="18" /></td>
<td>
<p>+66(0)83-455-1000</p>
<p>Monday - Saturday</p>
<p>9:00am - 6:00pm Bangkok Time</p>
</td>
</tr>
<tr>
<td style="text-align: right;"><img src="{{media url="wysiwyg/cms/line-icon.png"}}" alt="" width="18" /></td>
<td>nuBangkok</td>
</tr>
<tr>
<td style="text-align: right;"><img src="{{media url="wysiwyg/cms/locate-icon.png"}}" alt="" width="13" /></td>
<td>
<p>nuBangkok Head Office</p>
<p>199/74 Sukhumvit 8 Road, Khlong Toey,</p>
<p>Khlong Toey, Bangkok,</p>
<p>Thailand 10110</p>
</td>
</tr>
</tbody>
</table>
</div>
</div>
EOD
            ],
            'faq' => [
                'title' => 'FAQ',
                'identifier' => 'faq',
                'active' => true,
                'page_layout' => '2columns-left',
                'stores' => [0],
                'content' => <<<EOD
<div class="cms-faq">
<h1 class="cms-heading">FAQ</h1>
<hr class="cms-heading-line" />
<p><em>Lorem ipsum dolor sit amet?</em></p>
<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
<p><em>Lorem ipsum dolor sit amet?</em></p>
<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
<p><em>Lorem ipsum dolor sit amet?</em></p>
<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
<p><em>Lorem ipsum dolor sit amet?</em></p>
<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
<p><em>Lorem ipsum dolor sit amet?</em></p>
<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
</div>
EOD
            ],
            'policies' => [
                'title' => 'Policies',
                'identifier' => 'policies',
                'active' => true,
                'page_layout' => '2columns-left',
                'stores' => [0],
                'content' => <<<EOD
<div class="cms-policies">
<h1 class="cms-heading">Policies</h1>
<hr class="cms-heading-line" /><ol>
<li><span style="font-size: small;">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</span></li>
<li><span style="font-size: small;">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</span></li>
<li><span style="font-size: small;">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</span></li>
<li><span style="font-size: small;">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</span></li>
<li><span style="font-size: small;">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</span></li>
<li><span style="font-size: small;">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</span></li>
<li><span style="font-size: small;">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</span></li>
</ol>
</div>
EOD
            ],

            'customer-care' => [
                'title' => 'Customer Care',
                'identifier' => 'customer-care',
                'active' => true,
                'page_layout' => '2columns-left',
                'stores' => [0],
                'content' => <<<EOM
<div class="cms-customer-care">
<h1 class="cms-heading">Customer Care</h1>
<hr class="cms-heading-line" />
<div class="accordion-container">
<p class="accordion js-accordion">How to order <img style="padding-left: 10px; padding-bottom: 1px;" src="{{media url="wysiwyg/cms/arrow-up.png"}}" width="12" height="10" /></p>
<div class="panel">
<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
</div>
</div>
<div class="accordion-container">
<p class="accordion js-accordion">Shipping &amp; tracking <img style="padding-left: 10px; padding-bottom: 1px;" src="{{media url="wysiwyg/cms/arrow-up.png"}}" width="12" height="10" /></p>
<div class="panel">
<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
</div>
</div>
<div class="accordion-container">
<p class="accordion js-accordion">Return &amp; Exchange <img style="padding-left: 10px; padding-bottom: 1px;" src="{{media url="wysiwyg/cms/arrow-up.png"}}" width="12" height="10" /></p>
<div class="panel">
<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
</div>
</div>
<div class="accordion-container">
<p class="accordion js-accordion">Size Guide <img style="padding-left: 10px; padding-bottom: 1px;" src="{{media url="wysiwyg/cms/arrow-up.png"}}" width="12" height="10" /></p>
<div class="panel">
<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
</div>
</div>
<div class="accordion-container">
<p class="accordion js-accordion">FAQ <img style="padding-left: 10px; padding-bottom: 1px;" src="{{media url="wysiwyg/cms/arrow-up.png"}}" width="12" height="10" /></p>
<div class="panel">
<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
</div>
</div>
<p class="accordion"><a href="{{store url='product-care'}}" target="_self">Product Care <img style="padding-left: 10px;" src="{{media url="wysiwyg/cms/arrow-right.png"}}" width="9" height="11" /></a></p>
</div>
EOM
            ],
            'product-care' => [
                'title' => 'Product Care',
                'identifier' => 'product-care',
                'active' => true,
                'page_layout' => '2columns-left',
                'stores' => [0],
                'content' => <<<EOM
<div class="cms-product-care">
<h1 class="cms-heading">Product Care</h1>
<hr class="cms-heading-line" />
<div class="accordion-container">
<p class="accordion js-accordion">Shoes <img style="padding-left: 10px; padding-bottom: 1px;" src="{{media url="wysiwyg/cms/arrow-up.png"}}" width="12" height="10" /></p>
<div class="panel">
<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
</div>
</div>
<div class="accordion-container">
<p class="accordion js-accordion">Bags<img style="padding-left: 10px; padding-bottom: 1px;" src="{{media url="wysiwyg/cms/arrow-up.png"}}" width="12" height="10" /></p>
<div class="panel">
<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
</div>
</div>
</div>
EOM
            ]
        ];
        foreach ($pages as $pageData) {
            $pageObject = $this->pageFactory->create();
            $pageObject->addData($pageData);
            $this->pageRepository->save($pageObject);
        }
        return $this;
    }

    private function modify404pageRemoveUnusedPages()
    {
        $pageNoRoute = $this->pageRepository->getById('no-route');
        $pageNoRouteContent = <<<EOD
<div class="cms-404">
<h1 class="cms-heading">The page you requested was not found!</h1>
<hr class="cms-heading-line" />
<div style="padding: 30px 0;"><span style="font-size: 16px;"><strong><a class="violet-link" onclick="history.go(-1); return false;" href="#">Go back</a></strong> to the previous page.</span></div>
<div><span style="font-size: 16px;">Follow these links to get you back on track!</span></div>
<div><span style="font-size: 16px;"><strong><a class="violet-link" href="{{store url=""}}">Store Home</a> <span class="separator">|</span> <a class="violet-link" href="{{store url="customer/account"}}">My Account</a></strong></span></div>
</div>
EOD;
        $pageNoRoute->setContent($pageNoRouteContent);
        $pageNoRoute->setPageLayout('2columns-left');
        $this->pageRepository->save($pageNoRoute);

        $this->pageRepository->deleteById('enable-cookies');
        $this->pageRepository->deleteById('privacy-policy-cookie-restriction-mode');
        return $this;
    }

    private function updateContactUs()
    {
        $contactUs = $this->pageRepository->getById('contact-us');
        $content = <<<EOD
<div class="cms-contact-us">
<h3 class="cms-subheading">Need help?</h3>
<p>Check our <a style="text-decoration: underline;" href="{{store url='faq'}}">Frequently Asked Questions</a> section for help in finding an immediate answer to our most commonly asked questions.</p>
<p></p>
<p></p>
<p></p>
<h3 class="cms-subheading">Find shop?</h3>
<div style="overflow-x: auto;">
<table style="width: 600px;" border="0">
<tbody>
<tr>
<td style="padding-left: 0; width: 25%;" rowspan="6">OUTLET SHOPS</td>
<td>Ari (outside gate 3/4)</td>
<td>062-006-9553</td>
</tr>
<tr>
<td>Siam (inside exit 5)</td>
<td>094-547-9771</td>
</tr>
<tr>
<td>Ching Nonsi</td>
<td>099-454-8646</td>
</tr>
<tr>
<td>Chit Lom</td>
<td>094-547-9778</td>
</tr>
<tr>
<td>Nana</td>
<td>099-192-0019</td>
</tr>
<tr>
<td>Asok</td>
<td>094-454-9776</td>
</tr>
<tr>
<td rowspan="5"></td>
<td>Thong Lo</td>
<td>094-547-9775</td>
</tr>
<tr>
<td>Ekkamai</td>
<td>094-697-5160</td>
</tr>
<tr>
<td>Phra Khanong</td>
<td>099-391-7884</td>
</tr>
<tr>
<td>Udomsuk</td>
<td>092-050-0577</td>
</tr>
<tr>
<td>Bearing</td>
<td>094-649-9006</td>
</tr>
<tr>
<td colspan="3"><img src="{{media url="wysiwyg/cms/central-plaz.svg"}}" width="150" /><img style="padding-left: 20px;" src="{{media url="wysiwyg/cms/central-central.svg"}}" width="150" /></td>
</tr>
<tr>
<td rowspan="4"></td>
<td>Ubon Ratchathani</td>
<td>095-046-0492</td>
</tr>
<tr>
<td>Hat Yai</td>
<td>062-053-3811</td>
</tr>
<tr>
<td>Phitsanulok</td>
<td>061-995-8864</td>
</tr>
<tr>
<td>Chiang Rai</td>
<td>094-605-0530</td>
</tr>
</tbody>
</table>
</div>
<p></p>
<h3 class="cms-subheading">Customer Service</h3>
<div style="overflow-x: auto;">
<table border="0">
<tbody>
<tr>
<td style="text-align: right;"><img src="{{media url="wysiwyg/cms/phone-icon.png"}}" alt="" width="18" /></td>
<td>
<p>+66(0)83-455-1000</p>
<p>Monday - Saturday</p>
<p>9:00am - 6:00pm Bangkok Time</p>
</td>
</tr>
<tr>
<td style="text-align: right;"><img src="{{media url="wysiwyg/cms/line-icon.png"}}" alt="" width="18" /></td>
<td>nuBangkok</td>
</tr>
<tr>
<td style="text-align: right;"><img src="{{media url="wysiwyg/cms/locate-icon.png"}}" alt="" width="13" /></td>
<td>
<p>nuBangkok Head Office</p>
<p>199/74 Sukhumvit 8 Road, Khlong Toey,</p>
<p>Khlong Toey, Bangkok,</p>
<p>Thailand 10110</p>
</td>
</tr>
</tbody>
</table>
</div>
</div>
EOD;
        $contactUs->setContent($content);
        $this->pageRepository->save($contactUs);
        return $this;
    }


    private function updateLeftLinksSidebarBlock012()
    {
        $content = <<<EOD
        <div style="margin-bottom: 1rem;"><a class="violet-link" style="margin-bottom: 1rem;" href="{{store url='why-nu'}}">WHY <strong style="font-size: 18px;">nu</strong> ?</a></div>
<div style="margin-bottom: 1rem;"><a href="{{store url='blog'}}">INSPIRATION</a></div>
<div style="margin-bottom: 1rem;"><a href="{{store url='stories'}}">nu STORIES (yours and ours)</a></div>
<div style="margin-bottom: 1rem;"><a href="{{store url='customer-care'}}">CUSTOMER CARE</a></div>
<div style="margin-bottom: 1rem;"><a href="{{store url='product-care'}}">PRODUCT CARE</a></div>
<div style="margin-bottom: 1rem;"><a href="{{store url='contact-us'}}">CONTACT US</a></div>
<div><a href="{{store url='careers/listing/index'}}">CAREER</a></div>
<div class="social-links" style="margin-bottom: 1rem;">
<a class="social-acc-pic" style="padding-top: 0;" href="https://www.facebook.com" target="_blank"> <img src="{{view url=images/icons/homePage/Facebook.svg}}" alt="facebook-icon" /> </a> 
<a class="social-acc-pic" style="padding-top: 0;" href="https://www.instagram.com" target="_blank"> <img src="{{view url=images/icons/homePage/Instagram.svg}}" alt="instagram-icon" /> </a> 
<a class="social-acc-pic" style="padding-top: 0;" href="https://www.youtube.com" target="_blank"> <img src="{{view url=images/icons/homePage/youtube.svg}}" alt="youtube-icon" /> </a> 
<a class="social-acc-pic" style="padding-top: 0;"> <img src="{{view url=images/empty-link.png}}" alt="youtube-icon" /></a>
</div>
<div>
<p>#nuBangkok</p>
</div>
<div><a href="{{store url='policies'}}">POLICIES</a></div>
EOD;

        $block = $this->blockRepository->getById('left_sidebar_cms_static_block');
        $block->setContent($content);
        $this->blockRepository->save($block);
    }
    
    private function upgradeCmsPagesContactCustomerCareFaqPoliciesProductCare()
    {
        $pages = [
            'customer-care' => [
                'title' => 'Customer Care',
                'identifier' => 'customer-care',
                'active' => true,
                'page_layout' => '2columns-left',
                'stores' => [0],
                'content' => <<<EOM
<div class="cms-customer-care">
<h1 class="cms-heading">Customer Care</h1>
<hr class="cms-heading-line" />
<div class="accordion-container">
<p class="accordion js-accordion">How to order <img style="padding-left: 10px; padding-bottom: 1px;" src="{{media url="wysiwyg/cms/arrow-dropdown.svg"}}" width="12" height="10" /></p>
<div class="panel">
<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
</div>
</div>
<div class="accordion-container">
<p class="accordion js-accordion">Shipping &amp; tracking <img style="padding-left: 10px; padding-bottom: 1px;" src="{{media url="wysiwyg/cms/arrow-dropdown.svg"}}" width="12" height="10" /></p>
<div class="panel">
<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
</div>
</div>
<div class="accordion-container">
<p class="accordion js-accordion">Return &amp; Exchange <img style="padding-left: 10px; padding-bottom: 1px;" src="{{media url="wysiwyg/cms/arrow-dropdown.svg"}}" width="12" height="10" /></p>
<div class="panel">
<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
</div>
</div>
<div class="accordion-container">
<p class="accordion js-accordion">Size Guide <img style="padding-left: 10px; padding-bottom: 1px;" src="{{media url="wysiwyg/cms/arrow-dropdown.svg"}}" width="12" height="10" /></p>
<div class="panel">
<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
</div>
</div>
<div class="accordion-container">
<p class="accordion js-accordion">FAQ <img style="padding-left: 10px; padding-bottom: 1px;" src="{{media url="wysiwyg/cms/arrow-dropdown.svg"}}" width="12" height="10" /></p>
<div class="panel">
<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
</div>
</div>
<p class="accordion"><a href="{{store url='product-care'}}" target="_self">Product Care <img style="padding-left: 10px;" src="{{media url="wysiwyg/cms/arrow-right.svg"}}" width="9" height="11" /></a></p>
</div>
EOM
            ],
            'product-care' => [
                'title' => 'Product Care',
                'identifier' => 'product-care',
                'active' => true,
                'page_layout' => '2columns-left',
                'stores' => [0],
                'content' => <<<EOM
<div class="cms-product-care">
<h1 class="cms-heading">Product Care</h1>
<hr class="cms-heading-line" />
<div class="accordion-container">
<p class="accordion js-accordion">Shoes <img style="padding-left: 10px; padding-bottom: 1px;" src="{{media url="wysiwyg/cms/arrow-dropdown.svg"}}" width="12" height="10" /></p>
<div class="panel">
<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
</div>
</div>
<div class="accordion-container">
<p class="accordion js-accordion">Bags<img style="padding-left: 10px; padding-bottom: 1px;" src="{{media url="wysiwyg/cms/arrow-dropdown.svg"}}" width="12" height="10" /></p>
<div class="panel">
<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
</div>
</div>
</div>
EOM
            ]
        ];
        foreach ($pages as $pageData) {
            $pageObject = $this->pageFactory->create();
            $pageObject->addData($pageData);
            $this->pageRepository->save($pageObject);
        }
        return $this;

    }

    private function addCmsBlockOurStories()
    {
        $content = <<<EOD
        <style xml="space"><!--
@media only screen and (max-width: 772px) {
.cms-img-container {
width: 100%;
}
.cms-img-container p {
width: 100%;
display: block;
}

img {
width: 100%;
}
--></style>
<p>VISION</p>
<p style="padding-bottom: 10px;">A lifestyle brand that transforms the latest trends into accessible fashion, nuBangkok's product offerings have diversified to include bags, accessories and costume jewelry that inspire with experimental designs. The brand is the fashion destination for stylish urbanites and is synonymous with curated collections of relevant designs.</p>
<p></p>
<p></p>
<p style="text-align: center; padding-bottom: 20px;"><img src="{{media url="wysiwyg/_MG_0573.jpg"}}" width="80%" /></p>
<p></p>
<p style="font-size: 16px; padding-bottom: 10px;">What we do. (History)</p>
<p></p>
<p style="text-align: center;"><img src="{{media url="wysiwyg/_MG_0578.jpg"}}" width="80%" /></p>
<div class="cms-img-container" style="width: 80%; margin: 0 auto; padding-bottom: 20px;">
<p style="width: 49%; display: inline-block;"><img src="{{media url="wysiwyg/cms/career2.JPG"}}" /></p>
<p style="width: 49%; display: inline-block; float: right;"><img src="{{media url="wysiwyg/cms/career3.JPG"}}" /></p>
</div>
<p>A lifestyle brand that transforms the latest trends into accessible fashion, nuBangkok's product offerings have diversified to include bags, accessories and costume jewelry that inspire with experimental designs. The brand is the fashion destination for stylish urbanites and is synonymous with curated collections of relevant designs.</p>
EOD;
        $ourStoriesBlock = [
            'title' => 'Our stories',
            'identifier' => 'our_stories',
            'stores' => ['0'],
            'is_active' => 1,
            'content' => $content
        ];

        $block = $this->blockFactory->create(['data' => $ourStoriesBlock]);
        $this->blockRepository->save($block);
    }

    private function upgradeCmsPages()
    {
        $pages = [
            'home' => <<<EOD
<p><img src="{{media url="wysiwyg/01Homepage_1.jpg"}}" width="1200" height="auto" /></p>
<p><img src="{{media url="wysiwyg/02image-Ads1.jpg"}}" width="1200" height="auto" /></p>
<p><img src="{{media url="wysiwyg/03image-Ads2.jpg"}}" width="1200" height="auto" /></p>
<p><img src="{{media url="wysiwyg/04image-Ads3_1.jpg"}}" width="1200" height="auto" /></p>
EOD
            ,
            'why-nu' => <<<EOD
<div class="cms-why-nu-page">
<p class="page-title">OUR SHOES</p>
<table style="margin-bottom: 40px;" border="0">
<tbody>
<tr>
<td style="display: inline-block; padding-right: 25px;"><img src="{{media url='wysiwyg/cms/grip-icon.png'}}" width="35" /></td>
<td style="display: inline-block; padding-right: 50px; padding-top: 15px;">Grip</td>
<td style="display: inline-block;"></td>
<td style="display: inline-block; padding-right: 25px;"><img src="{{media url='wysiwyg/cms/grip-icon.png'}}" width="35" /></td>
<td style="display: inline-block; padding-top: 15px;">Grip</td>
</tr>
<tr>
<td style="display: inline-block; padding-right: 25px;"><img src="{{media url='wysiwyg/cms/grip-icon.png'}}" width="35" /></td>
<td style="display: inline-block; padding-right: 50px; padding-top: 15px;">Grip</td>
<td style="display: inline-block;"></td>
<td style="display: inline-block; padding-right: 25px;"><img src="{{media url='wysiwyg/cms/grip-icon.png'}}" width="35" /></td>
<td style="display: inline-block; padding-top: 15px;">Grip</td>
</tr>
<tr>
<td style="display: inline-block; padding-right: 25px;"><img src="{{media url='wysiwyg/cms/grip-icon.png'}}" width="35" /></td>
<td style="display: inline-block; padding-right: 50px; padding-top: 15px;">Grip</td>
<td style="display: inline-block;"></td>
<td style="display: inline-block; padding-right: 25px;"><img src="{{media url='wysiwyg/cms/grip-icon.png'}}" width="35" /></td>
<td style="display: inline-block; padding-top: 15px;">Grip</td>
</tr>
</tbody>
</table>
<p><img src="{{media url='wysiwyg/cms/why-nu1.jpg'}}" width="1500" height="999" /></p>
<p class="description">In the interests of hygiene we do not accept returns of, or refunds on earrings unless it is defective or a wrong item sent.</p>
<p><img style="margin-top: 40px;" src="{{media url='wysiwyg/cms/why-nu2.jpg'}}" width="1500" height="999" /></p>
<p class="description">In the interests of hygiene we do not accept returns of, or refunds on earrings unless it is defective or a wrong item sent.</p>
<p></p>
<p style="margin: 20px 0;">WE GUARANTEE</p>
<p></p>
<div style="margin-bottom: 10px;">
<div class="numberCircle" style="display: inline-block;">1</div>
<p style="display: inline-block; padding-left: 10px; width: 80%;">In the interests of hygiene we do not accept returns of.</p>
</div>
<div style="margin-bottom: 10px;">
<div class="numberCircle" style="display: inline-block;">2</div>
<p style="display: inline-block; padding-left: 10px; width: 80%;">A refunds on earrings unless it is defective or a wrong item sent.</p>
</div>
<div style="margin-bottom: 10px;">
<div class="numberCircle" style="display: inline-block;">3</div>
<p style="display: inline-block; padding-left: 10px; width: 80%;">A refunds on earrings unless it is defective or a wrong item sent.</p>
</div>
</div>


EOD
            ,
            'where-to-buy' => <<<EOD
<div class="cms-where-to-buy-page">
<div class="desktop-change-position-left">
<h1 class="cms-heading" style="margin-bottom: 30px;">WHERE TO BUY</h1>
<div class="accordion-container" style="border-top: 1px solid #55463e;">
<div class="accordion active js-revert-image-on-click" data-gmp-btn="false" data-target="#myImage" data-src="{{media url="wysiwyg/cms/outlet-mini-shops.jpg"}}">Outlet mini shops</div>
</div>
<div class="accordion-container">
<div class="accordion js-revert-image-on-click" data-gmp-btn="true" data-target="#myImage" data-src="{{media url="wysiwyg/cms/bangkok-map.png"}}">Thailand Malls</div>
</div>
<div class="accordion-container">
<div class="accordion js-revert-image-on-click" data-gmp-btn="false" data-target="#myImage" data-src="{{media url="wysiwyg/cms/nu-roadshow3.jpg"}}">Roadshows &amp; Events</div>
</div>
</div>
<div class="desktop-change-position-right"><img id="myImage" src="{{media url="wysiwyg/cms/outlet-mini-shops.jpg"}}" alt="" />
<p class="js-show-gm-btn" style="text-align: center; display: none;"><a class="cms-button" href="https://goo.gl/gk4eqW" target="_blank">Open with google map</a></p>
</div>
</div>
EOD
            ,
            'product-care' => <<<EOD
<div class="cms-product-care">
<h1 class="cms-heading">Product Care</h1>
<hr class="cms-heading-line" />
<div class="accordion-container">
<p class="accordion js-accordion">Shoes <img style="padding-left: 10px; padding-bottom: 1px;" src="{{media url="wysiwyg/cms/arrow-up.png"}}" width="12" height="10" /></p>
<div class="panel">
<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
</div>
</div>
<div class="accordion-container">
<p class="accordion js-accordion">Bags<img style="padding-left: 10px; padding-bottom: 1px;" src="{{media url="wysiwyg/cms/arrow-up.png"}}" width="12" height="10" /></p>
<div class="panel">
<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
</div>
</div>
</div>
EOD
            ,
            'customer-care' => <<<EOD
<div class="cms-customer-care">
<h1 class="cms-heading">Customer Care</h1>
<hr class="cms-heading-line" />
<div class="accordion-container">
<p class="accordion js-accordion">How to order <img style="padding-left: 10px; padding-bottom: 1px;" src="{{media url='wysiwyg/cms/arrow-up.png'}}" width="12" height="10" /></p>
<div class="panel">
<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
</div>
</div>
<div class="accordion-container">
<p class="accordion js-accordion">Shipping &amp; tracking <img style="padding-left: 10px; padding-bottom: 1px;" src="{{media url='wysiwyg/cms/arrow-up.png'}}" width="12" height="10" /></p>
<div class="panel">
<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
</div>
</div>
<div class="accordion-container">
<p class="accordion js-accordion">Return &amp; Exchange <img style="padding-left: 10px; padding-bottom: 1px;" src="{{media url='wysiwyg/cms/arrow-up.png'}}" width="12" height="10" /></p>
<div class="panel">
<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
</div>
</div>
<div class="accordion-container">
<p class="accordion js-accordion">Size Guide <img style="padding-left: 10px; padding-bottom: 1px;" src="{{media url='wysiwyg/cms/arrow-up.png'}}" width="12" height="10" /></p>
<div class="panel">
<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
</div>
</div>
<div class="accordion-container">
<p class="accordion js-accordion">FAQ <img style="padding-left: 10px; padding-bottom: 1px;" src="{{media url='wysiwyg/cms/arrow-up.png'}}" width="12" height="10" /></p>
<div class="panel">
<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
</div>
</div>
<p class="accordion"><a href="{{store url='product-care'}}" target="_self">Product Care <img style="padding-left: 10px;" src="{{media url='wysiwyg/cms/arrow-right.png'}}" width="9" height="11" /></a></p>
</div>
EOD
        ];

        foreach ($pages as $pageId => $content) {
            $page = $this->pageRepository->getById($pageId);
            $page->setContent($content);
            $this->pageRepository->save($page);
        }
        return $this;
    }

    private function upgradeLeftSidebarCmsBlock()
    {
        $content = <<<EOD
<div style="margin-bottom: 1rem;"><a class="violet-link" style="margin-bottom: 1rem;" href="{{store url="why-nu"}}">WHY <strong style="font-size: 18px;">nu</strong> ?</a></div>
<div style="margin-bottom: 1rem;"><a href="#">INSPIRATION</a></div>
<div style="margin-bottom: 1rem;"><a href="#">nu STORIES (yours and ours)</a></div>
<div style="margin-bottom: 1rem;"><a href="{{store url='customer-care'}}">CUSTOMER CARE</a></div>
<div style="margin-bottom: 1rem;"><a href="{{store url='product-care'}}">PRODUCT CARE</a></div>
<div style="margin-bottom: 1rem;"><a href="{{store url='contact-us'}}">CONTACT US</a></div>
<div><a href="{{store url='careers/listing/index'}}">CAREER</a></div>
<div class="social-links" style="margin-bottom: 1rem;"><a class="social-acc-pic" style="padding-top: 0;" href="https://www.facebook.com" target="_blank"> <img src="{{view url=images/facebook.png}}" alt="facebook-icon" /> </a> <a class="social-acc-pic" style="padding-top: 0;" href="https://www.instagram.com" target="_blank"> <img src="{{view url=images/instagram.png}}" alt="instagram-icon" /> </a> <a class="social-acc-pic" style="padding-top: 0;" href="https://www.youtube.com" target="_blank"> <img src="{{view url=images/youtube.png}}" alt="youtube-icon" /> </a></div>
<div>
<p>#nuBangkok</p>
</div>
<div><a href="{{store url='policies'}}">POLICIES</a></div>
EOD;

        $block = $this->blockRepository->getById('left_sidebar_cms_static_block');
        $block->setContent($content);
        $this->blockRepository->save($block);
        return $this;
    }

    private function upgradeComingSoon014()
    {
        $content = <<<EOD
<div class="cms-coming-soon-category">
<p><strong><span style="margin-bottom: 2rem;"><img src="{{media url="wysiwyg/05Women-clothes.jpg"}}" width="1000" height="288" />OUR WOMEN CLOTHING IS COMING LATER!</span></strong></p>
<p></p>
<p style="margin-bottom: 2rem;">Stay tuned!</p>
<p style="margin-bottom: 2rem;">If you would like to be notified when it launches please enter your email below!</p>
</div>
EOD;
        $block = $this->blockRepository->getById('coming_coon_category_women_clothing');
        $block->setContent($content);
        $this->blockRepository->save($block);
        return $this;
    }

    private function upgradeLeftMenu014()
    {
        $content = <<<EOD
<div class="nav-left-links"><a class="violet-link" href="{{store url="why-nu"}}">WHY <strong style="font-size: 18px;">nu</strong> ?</a></div>
<div class="nav-left-links"><a href="#">INSPIRATION</a></div>
<div class="nav-left-links"><a href="#">nu STORIES (yours and ours)</a></div>
<div class="nav-left-links"><a href="{{store url='customer-care'}}">CUSTOMER CARE</a></div>
<div class="nav-left-links"><a href="{{store url='product-care'}}">PRODUCT CARE</a></div>
<div class="nav-left-links"><a href="{{store url='contact-us'}}">CONTACT US</a></div>
<div class="nav-left-links"><a href="{{store url='careers/listing/index'}}">CAREER</a></div>
<div class="social-links"><a class="social-acc-pic" style="padding-top: 0;" href="https://www.facebook.com" target="_blank"> <img src="{{view url=images/facebook.png}}" alt="facebook-icon" /> </a> <a class="social-acc-pic" style="padding-top: 0;" href="https://www.instagram.com" target="_blank"> <img src="{{view url=images/instagram.png}}" alt="instagram-icon" /> </a> <a class="social-acc-pic" style="padding-top: 0;" href="https://www.youtube.com" target="_blank"> <img src="{{view url=images/youtube.png}}" alt="youtube-icon" /> </a></div>
<div class="nav-left-links">
<p style="margin-top: 3px; margin-bottom: 0;">#nuBangkok</p>
</div>
<div class="nav-left-links" style="margin-bottom: 0;"><a href="{{store url='policies'}}">POLICIES</a></div>
EOD;
        $block = $this->blockRepository->getById('left_sidebar_cms_static_block');
        $block->setContent($content);
        $this->blockRepository->save($block);
        return $this;
    }
}
