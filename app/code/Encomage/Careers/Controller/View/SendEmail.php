<?php

namespace Encomage\Careers\Controller\View;

use \Magento\Framework\App\Action;
use \Magento\Framework\Translate\Inline\StateInterface;
use \Magento\MediaStorage\Model\File\UploaderFactory;
use \Encomage\Careers\Model\Careers;

class SendEmail extends Action\Action
{
    const CAREERS_TEMPLATE_EMAIL = 'careers_email_settings/email/career_send_email';
    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;
    /**
     * @var UploaderFactory
     */
    protected $uploaderFactory;
    /**
     * @var Careers
     */
    protected $_careers;

    /**
     * SendEmail constructor.
     * @param Action\Context $context
     * @param StateInterface $inlineTranslation
     * @param UploaderFactory $uploaderFactory
     * @param Careers $careers
     */
    public function __construct(
        StateInterface $inlineTranslation,
        UploaderFactory $uploaderFactory,
        Action\Context $context,
        Careers $careers
    )
    {
        $this->inlineTranslation = $inlineTranslation;
        $this->uploaderFactory = $uploaderFactory;
        $this->_careers = $careers;
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
        try {
            $recipientData = ['name' => 'Admin', 'email' => $post['recipient']['email']];
            $file = $this->uploaderFactory->create(['fileId' => 'customer_cv'])->validateFile();
            $image = $this->uploaderFactory->create(['fileId' => 'customer_photo'])->validateFile();
            $this->inlineTranslation->suspend();
            $senderData = $this->_careers->validatedParams();
            $this->_careers->sendMail($senderData, $recipientData, $file, $image);
            $this->messageManager->addSuccess(
                __('Thanks for contacting us with your comments and questions. We\'ll respond to you very soon.')
            );
            $this->_redirect('*/listing/');
        } catch (\Exception $e) {
            $this->inlineTranslation->resume();
            $this->messageManager->addError(
                __('We can\'t process your request right now. Sorry, that\'s all we know.' . $e->getMessage())
            );
            $this->_redirect('*/listing/');
            return;
        }
    }
}