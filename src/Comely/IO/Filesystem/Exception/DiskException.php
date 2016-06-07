<?php
declare(strict_types=1);

namespace Comely\IO\Filesystem\Exception;

use Comely\IO\Filesystem\FsException;

/**
 * Class DiskException
 * @package Comely\IO\Filesystem\Exception
 */
class DiskException extends FsException
{
    protected static $componentId   =   "Comely\\IO\\Filesystem\\Disk";

    /**
     * @param string $message
     * @return DiskException
     */
    public static function diskInit(string $message) : DiskException
    {
        return new self(self::$componentId, $message, 1101);
    }

    /**
     * @param string $method
     * @param string $message
     * @return DiskException
     */
    public static function invalidPath(string $method, string $message) : DiskException
    {
        return new self($method, $message, 1102);
    }

    /**
     * @param string $method
     * @return DiskException
     */
    public static function readError(string $method) : DiskException
    {
        return new self($method, "Disk instance doesn't have reading privilege", 1103);
    }

    /**
     * @param string $method
     * @return DiskException
     */
    public static function writeError(string $method) : DiskException
    {
        return new self($method, "Disk instance doesn't have writing privilege", 1104);
    }

    /**
     * @param string $method
     * @param string $message
     * @return DiskException
     */
    public static function fsError(string $method, string $message) : DiskException
    {
        return new self($method, $message, 1105);
    }
}