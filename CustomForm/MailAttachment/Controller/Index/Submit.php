<?php
namespace CustomForm\MailAttachment\Controller\Index;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
class Submit extends \Magento\Framework\App\Action\Action
{
/**
* Recipient email config path
*/
const XML_PATH_EMAIL_RECIPIENT = 'trans_email/ident_custom2/email';
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
* @var \Magento\Framework\Escaper
*/
protected $_escaper;

protected $fileUploaderFactory;

protected $fileSystem;

protected $_resultRedirectFactory;
/**
* @param \Magento\Framework\App\Action\Context $context
* @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
* @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
* @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
* @param \Magento\Store\Model\StoreManagerInterface $storeManager
*/
public function __construct(
\Magento\Framework\App\Action\Context $context,
\Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
\Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
\Magento\Store\Model\StoreManagerInterface $storeManager,
\Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
Filesystem $fileSystem,

\Magento\Framework\Escaper $escaper
) {
parent::__construct($context);
$this->_transportBuilder = $transportBuilder;
$this->inlineTranslation = $inlineTranslation;
$this->scopeConfig = $scopeConfig;
$this->storeManager = $storeManager;
$this->fileUploaderFactory = $fileUploaderFactory;
$this->fileSystem          = $fileSystem;
$this->_escaper = $escaper;
$this->_resultRedirectFactory=$context->getResultRedirectFactory();
}

/**
* Post user question
*
* @return void
* @throws \Exception
*/
public function execute()
{
    $resultRedirect = $this->_resultRedirectFactory->create(); 
    $post = $this->getRequest()->getPostValue();
        if (!$post) {
            $this->_redirect('*/*/');
            return;
        }
        try {  
          
                $postObject = new \Magento\Framework\DataObject();
                $postObject->setData($post);
                $error = false;
                $filesData = $this->getRequest()->getFiles('document');
                if ($filesData['name']) {
                     $uploader = $this->fileUploaderFactory->create(['fileId' => 'document']);
                     $uploader->setAllowRenameFiles(true);
                     $uploader->setFilesDispersion(true);
                     $uploader->setAllowCreateFolders(true);
                     $path = $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('CustomImage');
                     $result = $uploader->save($path);
                     $upload_document = 'CustomImage'.$uploader->getUploadedFilename();
                     $filePath = $result['path'].$result['file'];
                     $fileName = $result['name'];
                } else {
                     $upload_document = '';
                     $filePath = '';
                     $fileName = '';
                }
                $sender = [
                    'name' => $this->_escaper->escapeHtml($post['firstname'].' '.$post['lastname']),
                    'email' => $this->_escaper->escapeHtml($post['email']),
                ];
                $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE; 
                $transport = $this->_transportBuilder->setTemplateIdentifier('send_email_custom_template') // this code we have mentioned in the email_templates.xml
                ->setTemplateOptions(
                        [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND, // this is using frontend area to get the template file
                        'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                        ]
                    )
                ->setTemplateVars(['data' => $postObject])
                ->setFrom($sender)
                ->addTo($this->scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT, $storeScope))
                ->addAttachment($filePath, $fileName)
                ->getTransport();
                $transport->sendMessage();
                $this->inlineTranslation->resume();
                $this->messageManager->addSuccess(__('Thanks for form submit.'));
                return $resultRedirect->setPath('*/*/');
            }catch (\Exception $e) {
                    $this->inlineTranslation->resume();
                    $this->messageManager->addError(__('We can\'t process your request right now. Sorry, that\'s all we know.'.$e->getMessage()));
                 return $resultRedirect->setPath('*/*/');
            }
    }


}

?>