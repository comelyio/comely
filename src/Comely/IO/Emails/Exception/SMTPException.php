<?php
declare(strict_types=1);

namespace Comely\IO\Emails\Exception;

/**
 * Class SMTPException
 * @package Comely\IO\Emails\Exception
 */
class SMTPException extends MailerException
{
    /** @var string */
    protected static $componentId   =   "Comely\\IO\\Emails\\Mailer\\SMTP";

    /**
     * @param int $num
     * @param string $error
     * @return SMTPException
     */
    public static function connectionError(int $num, string $error) : self
    {
        return new self(self::$componentId, sprintf('Connection Error: [%1$d] %2$s', $num, $error), 11011);
    }

    /**
     * @param string $command
     * @param int $expect
     * @param int $got
     * @return SMTPException
     */
    public static function unexpectedResponse(string $command, int $expect, int $got) : self
    {
        return new self(
            self::$componentId,
            sprintf('Expected "%2$d" from "%1$s" command, got "%3$d"', $command, $expect, $got),
            11012
        );
    }

    /**
     * @return SMTPException
     */
    public static function tlsNotAvailable() : self
    {
        return new self(self::$componentId, "TLS encryption is not available at remote SMTP server", 11013);
    }

    /**
     * @return SMTPException
     */
    public static function tlsNegotiateFailed() : self
    {
        return new self(self::$componentId, "TLS negotiation failed with remote SMTP server", 11014);
    }

    /**
     * @param string $error
     * @return SMTPException
     */
    public static function invalidRecipient(string $error) : self
    {
        return new self(
            self::$componentId,
            sprintf('Failed to set a recipient on remote SMTP server, "%1$s"', $error),
            11015
        );
    }

    /**
     * @return SMTPException
     */
    public static function authUnavailable() : self
    {
        return new self(self::$componentId, 'No supported authentication method available on remote SMTP server', 11016);
    }

    /**
     * @param string $error
     * @return SMTPException
     */
    public static function authFailed(string $error) : self
    {
        return new self(self::$componentId, sprintf('Authentication error "%1$s"', $error), 11017);
    }

    /**
     * @param int $size
     * @param int $max
     * @return SMTPException
     */
    public static function exceedsMaximumSize(int $size, int $max) : self
    {
        return new self(
            self::$componentId,
            sprintf('MIME (%1$d bytes) exceeds maximum size of %2$d', $size, $max),
            11018
        );
    }
}