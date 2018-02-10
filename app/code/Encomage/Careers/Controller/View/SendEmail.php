<?php

namespace Encomage\Careers\Controller\View;

use \Magento\Framework\App\Action\Context;
use \Magento\Framework\Mail\Template\TransportBuilder;
use \Magento\Framework\Translate\Inline\StateInterface;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\MediaStorage\Model\File\UploaderFactory;
use \Magento\Framework\DataObject;
use \Magento\Framework\Exception\LocalizedException;

use \Magento\Framework\Escaper;

class SendEmail extends \Magento\Framework\App\Action\Action
{
    const CAREERS_TEMPLATE_EMAIL = 'careers_email_settings/email/career_send_email';
    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;
    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var UploaderFactory
     */
    protected $uploaderFactory;
    /**
     * @var DataObject
     */
    protected $dataObject;
    /**
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper;

    /**
     * SendEmail constructor.
     * @param Context $context
     * @param TransportBuilder $transportBuilder
     * @param StateInterface $inlineTranslation
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param UploaderFactory $uploaderFactory
     * @param \Magento\Framework\DataObject $dataObject
     * @param Escaper $escaper
     */
    public function __construct(
        Context $context,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        UploaderFactory $uploaderFactory,
        DataObject $dataObject,
        Escaper $escaper
    )
    {
        $this->_transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->uploaderFactory = $uploaderFactory;
        $this->dataObject = $dataObject;
        $this->_escaper = $escaper;
        parent::__construct($context);
    }


    /**
     * @throws LocalizedException
     * @throws \Exception
     */
    public function execute()
    {
        $post = $this->getRequest()->getPostValue();
        if (!$post) {
            $this->_redirect('*/*/');
            return;
        }
        $file = null;
        $image = null;
        $senderData = $this->_validatedParams();
        $recipientData = ['name' => 'Admin', 'email' => $post['recipient']['email']];
//        $file = $this->uploaderFactory->create(['fileId' => 'customer_cv'])->validateFile();
//        $image = $this->uploaderFactory->create(['fileId' => 'customer_photo'])->validateFile();
        $this->inlineTranslation->suspend();
        try {
            $this->sendMail($senderData, $recipientData, $file, $image);
        } catch (\Exception $e) {
            $this->inlineTranslation->resume();
            $this->messageManager->addError(
                __('We can\'t process your request right now. Sorry, that\'s all we know.' . $e->getMessage())
            );
            $this->_redirect('*/listing/');
            return;
        }
    }

    /**
     * @param array $senderData
     * @param $recipientData
     * @param null $file
     * @param null $image
     */
    protected function sendMail(array $senderData, $recipientData, $file = null, $image = null)
    {
        $postObject = $this->dataObject->setData($senderData);
        $sender = [
            'name' => $this->_escaper->escapeHtml($senderData['name']),
            'email' => $this->_escaper->escapeHtml($senderData['email']),
        ];
        $storeId = $this->storeManager->getStore()->getId();
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $this->_transportBuilder->setTemplateIdentifier(
            $this->scopeConfig->getValue(self::CAREERS_TEMPLATE_EMAIL, $storeScope, $storeId)
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
        $this->inlineTranslation->resume();
        $this->messageManager->addSuccess(
            __('Thanks for contacting us with your comments and questions. We\'ll respond to you very soon.')
        );
        $this->_redirect('*/listing/');
        return;
    }

    /**
     * @return array
     * @throws LocalizedException
     * @throws \Exception
     */
    protected function _validatedParams()
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