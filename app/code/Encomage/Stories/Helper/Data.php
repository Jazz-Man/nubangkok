<?php
namespace Encomage\Stories\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Cache\StateInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Helper\Context;

class Data extends AbstractHelper
{
    /**
     * @var StateInterface
     */
    private $cacheState;
    /**
     * @var TypeListInterface
     */
    private $typeList;

    /**
     * Data constructor.
     * @param Context $context
     * @param StateInterface $cacheState
     * @param TypeListInterface $typeList
     */
    public function __construct(
        Context $context,
        StateInterface $cacheState,
        TypeListInterface $typeList
    )
    {
        $this->cacheState = $cacheState;
        $this->typeList = $typeList;
        parent::__construct($context);
    }
    
    /**
     * @return $this
     */
    public function invalidateCache()
    {
        if ($this->cacheState->isEnabled(\Magento\PageCache\Model\Cache\Type::TYPE_IDENTIFIER)) {
            $this->typeList->invalidate(['full_page']);
        }
        if ($this->cacheState->isEnabled(\Magento\Framework\App\Cache\Type\Block::TYPE_IDENTIFIER)) {
            $this->typeList->invalidate(['block_html']);
        }
        return $this;
    }
}