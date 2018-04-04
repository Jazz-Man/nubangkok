<?php
namespace Encomage\Stories\Controller\Yourstories;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Encomage\Stories\Api\StoriesRepositoryInterface;
use Encomage\Stories\Api\Data\StoriesInterfaceFactory;
use Encomage\Stories\Model\Stories;
use Magento\Framework\Filesystem;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Customer\Model\Session;
use Magento\Framework\Filesystem\Io\File;

/**
 * Class Save
 * @package Encomage\Stories\Controller\Yourstories
 */
class Save extends Action
{
    /**
     * @var StoriesRepositoryInterface
     */
    private $storiesRepository;
    /**
     * @var
     */
    private $storiesFactory;
    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    private $mediaDirectory;
    /**
     * @var DateTime
     */
    private $date;
    /**
     * @var File
     */
    private $ioFile;
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
     * @param File $ioFile
     */
    public function __construct(
        Context $context,
        StoriesRepositoryInterface $storiesRepository,
        Filesystem $filesystem,
        Session $customerSession,
        DateTime $date,
        StoriesInterfaceFactory $storiesFactory,
        File $ioFile
    )
    {
        $this->ioFile = $ioFile;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $this->storiesRepository = $storiesRepository;
        $this->customerSession = $customerSession;
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
        $params = $this->getRequest()->getParams();
        if (!empty($params)) {
            $customer = $this->customerSession->getCustomerData();
            $customerName = $customer->getFirstname() . ' ' . $customer->getLastname();
            /** @var \Encomage\Stories\Model\Stories $modelStory */
            $modelStory = $this->storiesFactory->create();
            $modelStory->setCustomerId($this->customerSession->getCustomerId());
            $modelStory->setCustomerName($customerName);
            $modelStory->setContent($params['content']);
            $modelStory->setCreatedAt($this->date->gmtDate());
            $modelStory->setTitle($params['title']);
            $dirPath = $this->mediaDirectory->getAbsolutePath(Stories::MEDIA_PATH_STORIES_IMAGE);
            $this->ioFile->checkAndCreateFolder($dirPath);
            try {
                $imageName = $this->_saveImage($params['story_image'], $dirPath);
                $modelStory->setImagePath(Stories::MEDIA_PATH_STORIES_IMAGE . $imageName);
                $this->storiesRepository->save($modelStory);
                $this->messageManager->addSuccessMessage(__('Your story hes been sent'));
            } catch (\Exception $e) {
                if (!empty($imageName)) {
                    @unlink($dirPath . $imageName);
                }
                $this->messageManager->addErrorMessage(__('Has not saved'));
            }

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

    /**
     * @param $imageDataJson
     * @param $dirPath
     * @return string
     */
    protected function _saveImage($imageDataJson, $dirPath)
    {
        $data = explode(';', $imageDataJson);
        $imageInfo = str_replace('data:', '', $data[0]);
        $imageInfo = explode('/', $imageInfo);
        $imageDataJson = str_replace('base64,', '', $data[1]);
        $imageDataJson = base64_decode(str_replace(' ', '+', $imageDataJson));

        $imageName = $this->_getImageName($imageInfo);
        $dirPath .= $imageName;
        file_put_contents($dirPath, $imageDataJson);
        return $imageName;
    }

    /**
     * @param $imageInfo
     * @return string
     */
    protected function _getImageName($imageInfo)
    {
        $newDate = new \DateTime();
        $imageName = $newDate->format('m_d_Y H_i_s') . '.' . $imageInfo[1];
        return $imageName;
    }
}