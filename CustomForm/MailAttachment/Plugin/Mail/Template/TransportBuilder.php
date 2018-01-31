<?php
namespace CustomForm\MailAttachment\Plugin\Mail\Template;

class TransportBuilder extends \Magento\Framework\Mail\Template\TransportBuilder
{
    public function addAttachment($file, $name)
    {
        if (!empty($file) && file_exists($file)) {
            $this->message
            ->createAttachment(
                file_get_contents($file),
                \Zend_Mime::TYPE_OCTETSTREAM,
                \Zend_Mime::DISPOSITION_ATTACHMENT,
                \Zend_Mime::ENCODING_BASE64,
                basename($name)
            );
        }
        return $this;
    }
}