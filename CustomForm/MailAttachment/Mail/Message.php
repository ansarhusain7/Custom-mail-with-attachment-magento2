<?php

namespace CustomForm\MailAttachment\Mail;

use Magento\Framework\Mail\MailMessageInterface;
use Zend\Mail\MessageFactory as MailMessageFactory;
use Zend\Mime\MessageFactory as MimeMessageFactory;
use Zend\Mime\Mime;
use Zend\Mime\Part;
use Zend\Mime\PartFactory;


class Message implements MailMessageInterface
{
    protected $partFactory;
    protected $mimeMessageFactory;
    private $zendMessage;
    private $messageType = self::TYPE_TEXT;
    protected $parts = [];

    public function __construct(PartFactory $partFactory, MimeMessageFactory $mimeMessageFactory, $charset = 'utf-8')
    {
        $this->partFactory = $partFactory;
        $this->mimeMessageFactory = $mimeMessageFactory;
        $this->zendMessage = MailMessageFactory::getInstance();
        $this->zendMessage->setEncoding($charset);
    }
    public function setBodyText($content)
    {
        $this->setMessageType(self::TYPE_TEXT);
        $textPart = $this->partFactory->create();

        $textPart->setContent($content)
            ->setType(Mime::TYPE_TEXT)
            ->setCharset($this->zendMessage->getEncoding());

        $this->parts[] = $textPart;

        return $this;
    }
    public function setBodyHtml($content)
    {
        $this->setMessageType(self::TYPE_HTML);
        $htmlPart = $this->partFactory->create();

        $htmlPart->setContent($content)
            ->setType(Mime::TYPE_HTML)
            ->setCharset($this->zendMessage->getEncoding());

        $this->parts[] = $htmlPart;

        $mimeMessage = new \Zend\Mime\Message();
        $mimeMessage->addPart($htmlPart);
        $this->zendMessage->setBody($mimeMessage);

        return $this;
    }
    public function setBodyAttachment($content, $fileName, $fileType, $encoding = '8bit')
    {
        $attachmentPart = $this->partFactory->create();

        $attachmentPart->setContent($content)
            ->setType($fileType)
            ->setFileName($fileName)
            ->setDisposition(Mime::DISPOSITION_ATTACHMENT)
            ->setEncoding($encoding);

        $this->parts[] = $attachmentPart;

        return $this;
    }
    public function setPartsToBody()
    {
        $mimeMessage = $this->mimeMessageFactory->create();
        $mimeMessage->setParts($this->parts);
        $this->zendMessage->setBody($mimeMessage);

        return $this;
    }
    public function setBody($body)
    {
        if (is_string($body) && $this->messageType === self::TYPE_HTML) {
            $body = self::createHtmlMimeFromString($body);
        }
        $this->zendMessage->setBody($body);

        return $this;
    }
    public function setSubject($subject)
    {
        $this->zendMessage->setSubject($subject);

        return $this;
    }
    public function getSubject()
    {
        return $this->zendMessage->getSubject();
    }
    public function getBody()
    {
        return $this->zendMessage->getBody();
    }
    public function setFrom($fromAddress)
    {
        $this->setFromAddress($fromAddress, null);

        return $this;
    }
    public function setFromAddress($fromAddress, $fromName = null)
    {
        $this->zendMessage->setFrom($fromAddress, $fromName);

        return $this;
    }
    public function addTo($toAddress)
    {
        $this->zendMessage->addTo($toAddress);

        return $this;
    }
    public function addCc($ccAddress)
    {
        $this->zendMessage->addCc($ccAddress);

        return $this;
    }
    public function addBcc($bccAddress)
    {
        $this->zendMessage->addBcc($bccAddress);

        return $this;
    }
    public function setReplyTo($replyToAddress)
    {
        $this->zendMessage->setReplyTo($replyToAddress);

        return $this;
    }
    public function getRawMessage()
    {
        return $this->zendMessage->toString();
    }
    private function createHtmlMimeFromString($htmlBody)
    {
        $htmlPart = new Part($htmlBody);
        $htmlPart->setCharset($this->zendMessage->getEncoding());
        $htmlPart->setType(Mime::TYPE_HTML);
        $mimeMessage = new \Zend\Mime\Message();
        $mimeMessage->addPart($htmlPart);

        return $mimeMessage;
    }
    public function setMessageType($type)
    {
        $this->messageType = $type;

        return $this;
    }
}
