<?php
declare(strict_types=1);

namespace Comely\IO\Emails\Mailer;

use Comely\IO\Emails\Message;

/**
 * Interface AgentInterface
 * @package Comely\IO\Emails\Mailer
 */
interface AgentInterface
{
    /**
     * @param Message $message
     * @param array $emails
     * @return int
     */
    public function send(Message $message, array $emails) : int;
}