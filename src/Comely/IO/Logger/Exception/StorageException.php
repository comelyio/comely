<?php
declare(strict_types=1);

namespace Comely\IO\Logger\Exception;

use Comely\IO\Logger\LoggerException;

/**
 * Class StorageException
 * @package Comely\IO\Logger\Exception
 */
class StorageException extends LoggerException
{
    protected static $componentId   =   "Comely\\IO\\Logger\\Storage";

    /**
     * @return StorageException
     */
    public static function diskPrivileges() : self
    {
        return new self(
            self::$componentId,
            'Disk instance must have read+write privileges',
            1301
        );
    }

    /**
     * @param string $method
     * @param string $message
     * @return StorageException
     */
    public static function writeError(string $method, string $message) : self
    {
        return new self(self::$componentId, sprintf('%s: %s', $method, $message), 1302);
    }
}