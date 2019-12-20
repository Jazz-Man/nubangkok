<?php


namespace Encomage\ErpIntegration\Helper;


use Encomage\ErpIntegration\Logger\Logger;
use function GuzzleHttp\json_decode as json_decodeAlias;
use function GuzzleHttp\json_encode as json_encodeAlias;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Filesystem;

/**
 * Class CacheFile
 *
 * @package ErpAPI\ErpAPICommand\Helper
 */
class CacheFile extends AbstractHelper
{

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface
     */
    private $cacheRead;
    /**
     * @var string
     */
    private $erpCacheFile;
    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    private $cacheWrite;
    /**
     * @var string
     */
    private $erpConfigProductFile;
    /**
     * @var \Encomage\ErpIntegration\Logger\Logger
     */
    private $logger;


    /**
     * CacheFile constructor.
     *
     * @param \Magento\Framework\App\Helper\Context     $context
     * @param \Encomage\ErpIntegration\Logger\Logger    $logger
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        Context $context,
        Logger $logger,
        ObjectManagerInterface $objectManager
    ) {
        parent::__construct($context);

        $filesystem    = $objectManager->get(Filesystem::class);
        $directoryList = $objectManager->get(DirectoryList::class);

        $this->erpCacheFile = 'erp/ErpCacheFile.json';

        $this->erpConfigProductFile = 'erp/ErpConfigProductFile.json';


        $this->cacheRead = $filesystem->getDirectoryRead($directoryList::VAR_DIR);

        try {
            $this->cacheWrite = $filesystem->getDirectoryWrite($directoryList::VAR_DIR);
        } catch (FileSystemException $e) {
            $this->cacheWrite = false;
        }
        $this->logger = $logger;
    }

    /**
     * @return bool|mixed
     */
    public function getConfigProduct()
    {
        return $this->getFileData($this->erpConfigProductFile);
    }

    /**
     * @return bool|mixed
     */
    public function getCacheFile()
    {
        return $this->getFileData($this->erpCacheFile);
    }

    /**
     * @param array $data
     */
    public function saveConfigProduct(array $data): void
    {
        $this->saveFileData($this->erpConfigProductFile, $data);
    }

    /**
     * @param array $data
     */
    public function saveCacheFile(array $data): void
    {
        $this->saveFileData($this->erpCacheFile, $data);
    }


    /**
     * @param string $file
     *
     * @return bool|mixed
     */
    private function getFileData(string $file)
    {
        try {

            $data = $this->cacheRead->isExist($file) ? $this->cacheRead->readFile($file) : false;

            return $data ? json_decodeAlias($data) : false;
        } catch (FileSystemException $e) {
            $this->logger->error('Get data from Cache File', [$e->getMessage()]);
        }

        return false;
    }


    /**
     * @param string $file
     * @param mixed  $data
     */
    private function saveFileData(string $file, $data): void
    {
        if ($this->cacheWrite) {
            try {
                $contents = json_encodeAlias($data, JSON_UNESCAPED_SLASHES);

                $this->cacheWrite->writeFile($file, $contents);
            } catch (FileSystemException $e) {
                $this->logger->error('Save data to Cache File', [$e->getMessage()]);

            }
        }
    }

}