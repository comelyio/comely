<?php
declare(strict_types=1);

namespace Comely\IO\Emails\Exception;

use Comely\IO\Emails\EmailsException;

/**
 * Class MailerException
 * @package Comely\IO\Emails\Mailer\Exception
 */
class MailerException extends EmailsException
{
    /** @var string */
    protected static $componentId   =   "Comely\\IO\\Emails\\Mailer";
}