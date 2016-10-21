<?php
declare(strict_types=1);

namespace Comely\IO\Emails;

use Comely\IO\Emails\Mailer\AgentInterface;
use Comely\IO\Emails\Mailer\Sendmail;

/**
 * Class Mailer
 * @package Comely\IO\Emails
 */
class Mailer
{
    /** @var AgentInterface */
    private $agent;
    /** @var string|null */
    private $senderName;
    /** @var string */
    private $senderEmail;

    /**
     * Mailer constructor.
     */
    public function __construct()
    {
        $this->agent    =   new Sendmail();
    }

    /**
     * @param AgentInterface $agent
     * @return Mailer
     */
    public function bindAgent(AgentInterface $agent) : self
    {
        $this->agent    =   $agent;
        return $this;
    }

    /**
     * @param string $name
     * @return Mailer
     */
    public function senderName(string $name) : self
    {
        $this->senderName   =   $name;
        return $this;
    }

    /**
     * @param string $email
     * @return Mailer
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
     * Compose new message
     *
     * @return Message
     */
    public function compose() : Message
    {
        return new Message($this);
    }

    /**
     * Send an email message and return number of emails sent
     * @param Message $message
     * @param \string[] ...$emails
     * @return int
     */
    public function send(Message $message, string ...$emails) : int
    {
        return $this->agent->send($message, $emails);
    }
}