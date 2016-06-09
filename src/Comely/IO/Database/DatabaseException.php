<?php
declare(strict_types=1);

namespace Comely\IO\Database;

/**
 * Class DatabaseException
 * @package Comely\IO\Database
 */
class DatabaseException extends \ComelyException
{
    protected static $componentId   =   __NAMESPACE__;

    /**
     * @param string $message
     * @return DatabaseException
     */
    public static function connectionError(string $message) : DatabaseException
    {
        return new self(self::$componentId, $message, 1101);
    }

    /**
     * @param string $method
     * @param string $message
     * @return DatabaseException
     */
    public static function queryError(string $method, string $message) : DatabaseException
    {
        return new self($method, $message, 1102);
    }

    /**
     * @param string $message
     * @return DatabaseException
     */
    public static function pdoError(string $message) : DatabaseException
    {
        return new self(self::$componentId, $message, 1103);
    }
}