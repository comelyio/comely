<?php
declare(strict_types=1);

namespace Comely\IO\Logger;

/**
 * Class LoggerException
 * @package Comely\IO\Logger
 */
class LoggerException extends \ComelyException
{
    /** @var string */
    protected static $componentId   =   __NAMESPACE__;

    /**
     * @param string $method
     * @return LoggerException
     */
    public static function invalidFlag(string $method) : self
    {
        return new self(
            self::$componentId,
            sprintf('Method "%1$s" was provided with an invalid flag', $method),
            1001
        );
    }
}