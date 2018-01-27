<?php
/**
 * This file is part of Comely IO package.
 * https://github.com/comelyio/comely
 *
 * Copyright (c) 2016-2018 Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/comelyio/comely/blob/master/LICENSE
 */

declare(strict_types=1);

namespace Comely\IO\Emails;

use Comely\IO\Emails\Exception\MessageException;
use Comely\IO\Emails\Message\Attachment;

/**
 * Class Message
 * @package Comely\IO\Emails
 */
class Message
{
    const EOL   =   "\r\n";

    /** @var array */
    private $attachments;
    /** @var null|string */
    private $bodyPlain;
    /** @var null|string */
    private $bodyHtml;
    /** @var array */
    private $headers;
    /** @var array */
    private $recipients;
    /** @var string */
    private $senderEmail;
    /** @var string|null */
    private $senderName;
    /** @var string */
    private $subject;

    /**
     * Message constructor.
     * @param Mailer $mailer
     */
    public function __construct(Mailer $mailer)
    {
        $this->attachments  =   [];
        $this->bodyPlain    =   null;
        $this->bodyHtml =   null;
        $this->headers  =   [];
        $this->recipients   =   [];
        $this->subject  =   "";

        $sender =   $mailer->getSender();
        $this->senderEmail  =   $sender[0];
        $this->senderName   =   $sender[1];
    }

    /**
     * Set sender's name
     *
     * @param string $name
     * @return Message
     */
    public function senderName(string $name) : self
    {
        $this->senderName   =   $name;
        return $this;
    }

    /**
     * Set sender's email address
     *
     * @param string $email
     * @return Message
     * @throws EmailsException
     */
    public function senderEmail(string $email) : self
    {
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw EmailsException::badEmailAddress(__METHOD__);
        }

        $this->senderEmail  =   $email;
        return $this;
    }

    /**
     * Get sender name and email as indexed array
     *
     * @return array
     */
    public function getSender() : array
    {
        return [$this->senderEmail, $this->senderName];
    }

    /**
     * Set subject for email message
     *
     * @param string $subject
     * @return Message
     */
    public function subject(string $subject) : self
    {
        $this->subject  =   $subject;
        return $this;
    }

    /**
     * Get subject
     *
     * @return string
     */
    public function getSubject() : string
    {
        return $this->subject;
    }

    /**
     * Set text/plain body for email message
     *
     * @param string $body
     * @return Message
     */
    public function bodyPlain(string $body) : self
    {
        $this->bodyPlain    =   $body;
        return $this;
    }

    /**
     * Set text/html body for email message
     *
     * @param string $body
     * @return Message
     */
    public function bodyHtml(string $body) : self
    {
        $this->bodyHtml =   $body;
        return $this;
    }

    /**
     * Set an email header
     *
     * @param string $key
     * @param string $value
     * @return Message
     * @throws MessageException
     */
    public function setHeader(string $key, string $value) : self
    {
        if(in_array(strtolower($key), ["from", "subject", "content-type", "x-mailer"])) {
            throw MessageException::disabledHeaderKey($key);
        }

        $this->headers[$key]    =   $value;
        return $this;
    }

    /**
     * @param string $filePath
     * @param string|null $type
     * @return Attachment
     */
    public function attach(string $filePath, string $type = null) : Attachment
    {
        $attachment =   new Attachment($filePath, $type);
        $this->attachments[]    =   $attachment;
        return $attachment;
    }

    /**
     * Get compiled email in MIME format
     *
     * @param string $separator
     * @return string
     */
    public function getCompiled(string $separator = "") : string
    {
        // Boundaries
        $uniqueId   =   md5(uniqid(sprintf("%s-%s", $this->subject, microtime(false))));
        $boundaries[]   =   "--Comely_B1" . $uniqueId;
        $boundaries[]   =   "--Comely_B2" . $uniqueId;
        $boundaries[]   =   "--Comely_B3" . $uniqueId;

        // Headers
        $headers[]  =   !empty($this->senderName) ?
            sprintf('From: %1$s <%2$s>', $this->senderName, $this->senderEmail) :
            sprintf('From:<%1$s>', $this->senderEmail);
        $headers[]  =   sprintf('Subject: %1$s', $this->subject);
        $headers[]  =   "MIME-Version: 1.0";
        $headers[]  =   sprintf('X-Mailer: Comely %1$s', \Comely::VERSION);
        $headers[]  =   sprintf('Content-Type: multipart/mixed; boundary="%1$s"', substr($boundaries[0], 2));
        foreach($this->headers as $key => $value) {
            $headers[]  =   sprintf('%1$s: %2$s', $key, $value);
        }

        $headers[]  =   $separator; // Separator line between headers and body

        // Body
        $body[] =   "This is a multi-part message in MIME format.";
        $body[] =   $boundaries[0];
        $body[] =   sprintf('Content-Type: multipart/alternative; boundary="%1$s"', substr($boundaries[1], 2));
        $body[] =   ""; // Empty line

        // Body: text/plain
        if($this->bodyPlain) {
            $encoding   =   $this->checkBodyEncoding($this->bodyPlain);
            $body[] =   $boundaries[1];
            $body[] =   sprintf('Content-Type: text/plain; charset=%1$s', $encoding[0]);
            $body[] =   sprintf('Content-Transfer-Encoding: %1$s', $encoding[1]);
            $body[] =   ""; // Empty line
            $body[] =   $this->bodyPlain;
        }

        // Body: text/html
        if($this->bodyHtml) {
            $encoding   =   $this->checkBodyEncoding($this->bodyHtml);
            $body[] =   $boundaries[1];
            $body[] =   sprintf('Content-Type: text/html; charset=%1$s', $encoding[0]);
            $body[] =   sprintf('Content-Transfer-Encoding: %1$s', $encoding[1]);
            $body[] =   ""; // Empty line
            $body[] =   $this->bodyHtml;
        }

        // Attachments
        foreach($this->attachments as $attachment) {
            /** @var $attachment Attachment */
            $body[] =   $boundaries[0];
            $body[] =   implode(self::EOL, $attachment->getMime());
        }

        // Compile
        $mime   =   array_merge($headers, $body);
        return implode(self::EOL, $mime);
    }

    /**
     * @param string $body
     * @return array
     */
    private function checkBodyEncoding(string $body) : array
    {
        return preg_match("/[\x80-\xFF]/", $body) ? ["utf-8", "8Bit"] : ["us-ascii", "7Bit"];
    }
}