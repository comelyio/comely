<?php
declare(strict_types=1);

namespace Comely\IO\Emails;

/**
 * Class EmailsException
 * @package Comely\IO\Emails
 */
class EmailsException extends \ComelyException
{
    /** @var string */
    protected static $componentId   =   __NAMESPACE__;

    /**
     * @param string $method
     * @return EmailsException
     */
    public static function badEmailAddress(string $method) : self
    {
        return new self(
            $method,
            sprintf('Invalid e-mail address'),
            1001
        );
    }
}