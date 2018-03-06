<?php
namespace Encomage\Stories\Controller\Yourstories;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Encomage\Stories\Api\StoriesRepositoryInterface;
use Encomage\Stories\Api\Data\StoriesInterfaceFactory;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Customer\Model\Session;

/**
 * Class Save
 * @package Encomage\Stories\Controller\Yourstories
 */
class Save extends Action
{
    const MEDIA_PATH_STORIES_IMAGE = 'stories/';
    /**
     * @var StoriesRepositoryInterface
     */
    private $storiesRepository;
    /**
     * @var
     */
    private $storiesFactory;
    /**
     * @var UploaderFactory
     */
    private $uploaderFactory;
    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    private $mediaDirectory;
    /**
     * @var DateTime
     */
    private $date;
    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * Save constructor.
     * @param Context $context
     * @param StoriesRepositoryInterface $storiesRepository
     * @param Filesystem $filesystem
     * @param Session $customerSession
     * @param DateTime $date
     * @param StoriesInterfaceFactory $storiesFactory
     * @param UploaderFactory $uploaderFactory
     */
    public function __construct(
        Context $context,
        StoriesRepositoryInterface $storiesRepository,
        Filesystem $filesystem,
        Session $customerSession,
        DateTime $date,
        StoriesInterfaceFactory $storiesFactory,
        UploaderFactory $uploaderFactory
    )
    {
        $this->mediaDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $this->storiesRepository = $storiesRepository;
        $this->customerSession = $customerSession;
        $this->uploaderFactory = $uploaderFactory;
        $this->storiesFactory = $storiesFactory;
        $this->date = $date;
        parent::__construct($context);
    }

    /**
     * @return $this
     */
    public function execute()
    {
        if (!$this->_isCustomerLogged()) {
            $this->messageManager->addErrorMessage(__('You are not authorized.'));
            $this->_redirect('stories');
        }
        $uploader = null;
        if (!empty($_FILES['story_image']['tmp_name'])) {
            /** @var \Magento\MediaStorage\Model\File\Uploader $uploader */
            $uploader = $this->uploaderFactory->create(['fileId' => 'story_image']);
        }
        $params = $this->getRequest()->getParams();
        if (!empty($params)) {
            /** @var \Encomage\Stories\Model\Stories $modelStory */
            $modelStory = $this->storiesFactory->create();
            $modelStory->setCustomerId($params['customer_id']);
            $modelStory->setContent($params['content']);
            $modelStory->setCreatedAt($this->date->gmtDate());
            if ($uploader) {
                $target = $this->mediaDirectory->getAbsolutePath(self::MEDIA_PATH_STORIES_IMAGE);
                $uploader->setAllowRenameFiles(true);
                $result = $uploader->save($target);
                $modelStory->setImagePath(self::MEDIA_PATH_STORIES_IMAGE . $result['file']);
            }
            $this->storiesRepository->save($modelStory);
            $this->messageManager->addSuccessMessage(__('Your story hes been sent'));
        } else {
            $this->messageManager->addErrorMessage(__('No data'));
        }
        $this->_redirect('stories');
        return $this;
    }

    /**
     * @return bool
     */
    protected function _isCustomerLogged()
    {
        return $this->customerSession->isLoggedIn();
    }
}