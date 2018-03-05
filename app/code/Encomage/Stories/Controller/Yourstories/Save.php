<?php
namespace Encomage\Stories\Controller\Yourstories;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Encomage\Stories\Api\StoriesRepositoryInterface;
use Encomage\Stories\Api\Data\StoriesInterfaceFactory;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Stdlib\DateTime\DateTime;

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
     * Save constructor.
     * @param Context $context
     * @param StoriesRepositoryInterface $storiesRepository
     * @param Filesystem $filesystem
     * @param DateTime $date
     * @param StoriesInterfaceFactory $storiesFactory
     * @param UploaderFactory $uploaderFactory
     */
    public function __construct(
        Context $context,
        StoriesRepositoryInterface $storiesRepository,
        Filesystem $filesystem,
        DateTime $date,
        StoriesInterfaceFactory $storiesFactory,
        UploaderFactory $uploaderFactory
    )
    {
        $this->mediaDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $this->storiesRepository = $storiesRepository;
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
        $uploader = null;
        if (!empty($_FILES['story_image']['tmp_name'])) {
            $uploader = $this->uploaderFactory->create(['fileId' => 'story_image']);
        }
        $params = $this->getRequest()->getParams();
        if (!empty($params)) {
            if ($uploader) {
                $target = $this->mediaDirectory->getAbsolutePath(self::MEDIA_PATH_STORIES_IMAGE);
                $uploader->setAllowRenameFiles(true);
                $result = $uploader->save($target);
                $params['image_path'] = self::MEDIA_PATH_STORIES_IMAGE . $result['file'];
            }
            $params['created_at'] = $this->date->gmtDate();
            $modelStory = $this->storiesFactory->create();
            $modelStory->setStory($params);
            $this->storiesRepository->save($modelStory);
            $this->messageManager->addSuccessMessage(__('Your story hes been sent'));
        } else {
            $this->messageManager->addErrorMessage(__('No data'));
        }
        $this->_redirect('stories');
        return $this;
    }
}