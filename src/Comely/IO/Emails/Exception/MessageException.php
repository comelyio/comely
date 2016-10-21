<?php
declare(strict_types=1);

namespace Comely\IO\Emails\Exception;

use Comely\IO\Emails\EmailsException;

/**
 * Class MessageException
 * @package Comely\IO\Emails\Mailer\Exception
 */
class MessageException extends EmailsException
{
    /** @var string */
    protected static $componentId   =   "Comely\\IO\\Emails\\Message";

    /**
     * @param string $key
     * @return MessageException
     */
    public static function disabledHeaderKey(string $key) : self
    {
        return new self(self::$componentId, sprintf('Use appropriate method instead to set "%1$s" header', $key), 1101);
    }

    /**
     * @param string $method
     * @param string $file
     * @return MessageException
     */
    public static function attachmentUnreadable(string $method, string $file) : self
    {
        return new self(
            $method,
            sprintf('Attached file "%1$s" is unreadable', basename($file)),
            1102
        );
    }
}