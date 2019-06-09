<?php

namespace Encomage\DropdownFields\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action;
use Magento\Framework\File\Csv;
use Magento\Framework\App\Filesystem\DirectoryList;
use Encomage\DropdownFields\Model\ResourceModel\Country;

/**
 * Class Upload
 * @package Encomage\DropdownFields\Controller\Adminhtml\Index
 */
class Upload extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Encomage_DropdownFields::dropdown_fields_config';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Csv
     */
    protected $csvProccesor;

    /**
     * @var DirectoryList
     */
    protected $directoryList;

    /**
     * @var Country
     */
    protected $countryResourceModel;

    /**
     * @var
     */
    protected $resultRedirect;

    /**
     * Upload constructor.
     * @param Context $context
     * @param Csv $csvProccesor
     * @param PageFactory $resultPageFactory
     * @param DirectoryList $directoryList
     * @param Country $countryResourceModel
     */
    public function __construct(
        Context $context,
        Csv $csvProccesor,
        PageFactory $resultPageFactory,
        DirectoryList $directoryList,
        Country $countryResourceModel
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->countryResourceModel = $countryResourceModel;
        $this->directoryList = $directoryList;
        $this->csvProccesor = $csvProccesor;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $url = $this->_redirect->getRefererUrl();
        $resultRedirect->setUrl($url);

        $this->readCsv();

        return $resultRedirect;
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function readCsv()
    {
        $pubMediaDir = $this->directoryList->getPath(DirectoryList::PUB);
        $fieName = 'countryData.csv';
        $ds = DIRECTORY_SEPARATOR;
        $dirTest = '/upload_country';

        $file = $pubMediaDir . $dirTest . $ds . $fieName;
        $countryInsertData = [];
        if (!empty($file)) {
            $csvData = $this->csvProccesor->getData($file);
            foreach ($csvData as $key => $dataRow) {
                if ($key < 1) {
                    continue;
                }
                if (!empty($dataRow[0]) &&
                    !empty($dataRow[1]) &&
                    !empty($dataRow[2]) &&
                    !empty($dataRow[3])
                ) {
                    $countryInsertData[] = [
                        'country_code' => $dataRow[0],
                        'country_name' => $dataRow[1],
                        'region' => $dataRow[2],
                        'city' => $dataRow[3]
                    ];
                }

            }
        }
        $this->countryResourceModel->getConnection()->insertMultiple(
            $this->countryResourceModel->getMainTable(),
            $countryInsertData
        );
    }

    /**
     * @return bool
     */
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed(self::ADMIN_RESOURCE);
    }
}


