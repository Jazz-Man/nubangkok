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
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;
    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    private $directoryList;
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
     * CacheFile constructor.
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {

        $this->filesystem    = $objectManager->get(Filesystem::class);
        $this->directoryList = $objectManager->get(DirectoryList::class);

        $this->erpCacheFile = 'erp/ErpCacheFile.json';


        $this->cacheRead = $this->filesystem->getDirectoryRead($this->directoryList::CACHE);
        $this->cacheWrite = $this->filesystem->getDirectoryWrite($this->directoryList::CACHE);
    }

    /**
     * @return bool|mixed
     */
    public function getCacheFile()
    {
        try {

            $data = $this->cacheRead->isExist($this->erpCacheFile) ? $this->cacheRead->readFile($this->erpCacheFile) : false;

            return $data ? json_decodeAlias($data) : false;
        } catch (FileSystemException $e) {
            dump($e->getMessage());
        }

        return false;
    }

    /**
     * @param array $data
     */
    public function saveCacheFile(array $data){
        try {
            $contents = json_encodeAlias($data, JSON_UNESCAPED_SLASHES);

            $this->cacheWrite->writeFile($this->erpCacheFile, $contents);
        } catch (FileSystemException $e) {
            dump($e->getMessage());
        }
    }

}