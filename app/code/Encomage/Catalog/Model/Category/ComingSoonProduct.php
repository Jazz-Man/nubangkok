<?php

namespace Encomage\Catalog\Model\Category;

use Magento\Framework\App\Area;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Encomage\Catalog\Model\ResourceModel\Category\ComingSoonProduct as ComingSoonProductResource;
use Encomage\Catalog\Model\ResourceModel\Category\ComingSoon\Collection as ComingSoonProductResourceCollection;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Translate\Inline\StateInterface;

/**
 * Class ComingSoonProduct
 *
 * @package Encomage\Catalog\Model\Category
 */
class ComingSoonProduct extends AbstractModel
{
    private $_dataObject;
    private $_escaper;
    private $_transportBuilder;
    private $_storeManager;
    private $_scopeConfig;
    private $_inlineTranslation;

    /**
     * ComingSoonProduct constructor.
     * @param Context $context
     * @param Registry $registry
     * @param StateInterface $inlineTranslation
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param DataObject $dataObject
     * @param Escaper $escaper
     * @param TransportBuilder $transportBuilder
     * @param ComingSoonProductResource $resource
     * @param ComingSoonProductResourceCollection $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        StateInterface $inlineTranslation,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        DataObject $dataObject,
        Escaper $escaper,
        TransportBuilder $transportBuilder,
        ComingSoonProductResource $resource,
        ComingSoonProductResourceCollection $resourceCollection,
        array $data = [])
    {
        $this->_dataObject = $dataObject;
        $this->_escaper = $escaper;
        $this->_transportBuilder = $transportBuilder;
        $this->_storeManager = $storeManager;
        $this->_scopeConfig = $scopeConfig;
        $this->_inlineTranslation = $inlineTranslation;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data);
    }

    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init(ComingSoonProductResource::class);
    }

    /**
     * @param array $senderData
     * @param       $recipientData
     * @param       $template
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function sendEmail(array $senderData, $recipientData, $template)
    {
        $postObject = $this->_dataObject->setData($senderData);
        $sender = [
            'name' => $this->_escaper->escapeHtml($senderData['name']),
            'email' => $this->_escaper->escapeHtml($senderData['email']),
        ];
        $storeId = $this->_storeManager->getStore()->getId();
        $transporter =$this->_transportBuilder->setTemplateIdentifier($template)
            ->setTemplateOptions(['area' => Area::AREA_FRONTEND, 'store' => $storeId])
            ->setTemplateVars(['data' => $postObject])
            ->setFrom($sender)
            ->addTo($recipientData)
            ->getTransport();
        $transporter->sendMessage();
        $this->_inlineTranslation->resume();
    }
}
