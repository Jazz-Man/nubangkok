<?php


namespace Encomage\ErpIntegration\Helper;


use function GuzzleHttp\json_decode as json_decodeAlias;
use function GuzzleHttp\json_encode as json_encodeAlias;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Filesystem;

/**
 * Class CacheFile
 *
 * @package ErpAPI\ErpAPICommand\Helper
 */
class CacheFile
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
     * CacheFile constructor.
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     *
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {

        $filesystem    = $objectManager->get(Filesystem::class);
        $directoryList = $objectManager->get(DirectoryList::class);

        $this->erpCacheFile = 'erp/ErpCacheFile.json';

        $this->erpConfigProductFile = 'erp/ErpConfigProductFile.json';


        $this->cacheRead  = $filesystem->getDirectoryRead($directoryList::VAR_DIR);

        try {
            $this->cacheWrite = $filesystem->getDirectoryWrite($directoryList::VAR_DIR);
        } catch (FileSystemException $e) {
            $this->cacheWrite = false;
        }
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
            dump($e->getMessage());
        }

        return false;
    }


    /**
     * @param string $file
     * @param mixed  $data
     */
    private function saveFileData(string $file, $data): void
    {
        if ($this->cacheWrite){
            try {
                $contents = json_encodeAlias($data, JSON_UNESCAPED_SLASHES);

                $this->cacheWrite->writeFile($file, $contents);
            } catch (FileSystemException $e) {
                dump($e->getMessage());
            }
        }
    }

}