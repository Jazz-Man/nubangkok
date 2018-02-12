<?php
namespace Encomage\Careers\Model;

use \Magento\Framework\Model;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Framework\Translate\Inline\StateInterface;
use \Magento\Framework\Mail\Template\TransportBuilder;
use \Magento\Framework\Exception\LocalizedException;
use \Magento\Framework\Data\Collection\AbstractDb;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Framework\DataObject;
use \Magento\Framework\Registry;
use \Magento\Framework\Escaper;


class Careers extends Model\AbstractModel implements DataObject\IdentityInterface
{
    const CACHE_TAG = 'encomage_careers';

    protected $_cacheTag = 'encomage_careers';

    protected $_eventPrefix = 'encomage_careers';
    /**
     * @var Escaper
     */
    protected $_escaper;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;
    /**
     * @var DataObject
     */
    protected $_dataObject;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var TransportBuilder
     */
    protected $_transportBuilder;
    /**
     * @var StateInterface
     */
    protected $_inlineTranslation;

    /**
     * Careers constructor.
     * @param Model\Context $context
     * @param Registry $registry
     * @param Model\ResourceModel\AbstractResource $resource
     * @param AbstractDb $resourceCollection
     * @param array $data
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param TransportBuilder $transportBuilder
     * @param StateInterface $inlineTranslation
     * @param DataObject $dataObject
     * @param Escaper $escaper
     */
    public function __construct(
        Model\Context $context,
        Registry $registry,
        Model\ResourceModel\AbstractResource $resource,
        AbstractDb $resourceCollection,
        array $data,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        DataObject $dataObject,
        Escaper $escaper
    )
    {
        $this->_inlineTranslation = $inlineTranslation;
        $this->_transportBuilder = $transportBuilder;
        $this->_storeManager = $storeManager;
        $this->_scopeConfig = $scopeConfig;
        $this->_dataObject = $dataObject;
        $this->_escaper = $escaper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    protected function _construct()
    {
        $this->_init('Encomage\Careers\Model\ResourceModel\Careers');
    }

    /**
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @param array $senderData
     * @param $recipientData
     * @param null $file
     * @param null $image
     */
    public function sendMail(array $senderData, $recipientData, $file = null, $image = null)
    {
        $postObject = $this->_dataObject->setData($senderData);
        $sender = [
            'name' => $this->_escaper->escapeHtml($senderData['name']),
            'email' => $this->_escaper->escapeHtml($senderData['email']),
        ];
        $storeId = $this->_storeManager->getStore()->getId();
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $this->_transportBuilder->setTemplateIdentifier(
            $this->_scopeConfig->getValue(\Encomage\Careers\Controller\View\SendEmail::CAREERS_TEMPLATE_EMAIL, $storeScope, $storeId)
        )
            ->setTemplateOptions(['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId,])
            ->setTemplateVars(['data' => $postObject])
            ->setFrom($sender)
            ->addTo($recipientData);
        if ($file) {
            $this->_transportBuilder->addAttachment(file_get_contents($file['tmp_name']), null, null, null, $file['name']);
        }
        if ($image) {
            $this->_transportBuilder->addAttachment(file_get_contents($image['tmp_name']), null, null, null, $image['name']);
        }
        $transporter = $this->_transportBuilder->getTransport();
        $transporter->sendMessage();
        $this->_inlineTranslation->resume();
        return;
    }

    /**
     * @return array
     * @throws LocalizedException
     * @throws \Exception
     */
    public function validatedParams()
    {
        $request = $this->getRequest()->getParam('customer');
        if (trim($request['lastName']) === '') {
            throw new LocalizedException(__('Last Name is missing'));
        }
        if (trim($request['firstName']) === '') {
            throw new LocalizedException(__('First Name is missing'));
        }
        if (trim($request['message']) === '') {
            throw new LocalizedException(__('Message is missing'));
        }
        if (false === \strpos($request['email'], '@')) {
            throw new LocalizedException(__('Invalid email address'));
        }
        if (trim($this->getRequest()->getParam('hideit')) !== '') {
            throw new \Exception();
        }
        $result = [
            'name' => $request['firstName'] . ' ' . $request['lastName'],
            'email' => $request['email'],
            'message' => $request['message']
        ];
        return $result;
    }
}