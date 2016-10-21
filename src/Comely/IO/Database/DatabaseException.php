<?php
declare(strict_types=1);

namespace Comely\IO\Database;

/**
 * Class DatabaseException
 * @package Comely\IO\Database
 */
class DatabaseException extends \ComelyException
{
    /** @var string */
    protected static $componentId   =   __NAMESPACE__;

    /**
     * @param string $message
     * @return DatabaseException
     */
    public static function connectionError(string $message) : self
    {
        return new self(self::$componentId, $message, 1001);
    }

    /**
     * @param string $method
     * @param string $message
     * @return DatabaseException
     */
    public static function queryError(string $method, string $message) : self
    {
        return new self($method, $message, 1002);
    }

    /**
     * @param string $message
     * @return DatabaseException
     */
    public static function pdoError(string $message) : self
    {
        return new self(self::$componentId, $message, 1003);
    }
}