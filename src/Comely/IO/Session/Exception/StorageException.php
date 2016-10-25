<?php
declare(strict_types=1);

namespace Comely\IO\Session\Exception;

use Comely\IO\Session\SessionException;

/**
 * Class StorageException
 * @package Comely\IO\Logger\Exception
 */
class StorageException extends SessionException
{
    /** @var string */
    protected static $componentId   =   "Comely\\IO\\Session\\Storage";

    /**
     * @return StorageException
     */
    public static function diskPrivileges() : self
    {
        return new self(self::$componentId, 'Disk instance does not have read+write privileges', 1101);
    }

    /**
     * @param string $method
     * @param string $error
     * @return StorageException
     */
    public static function readError(string $method, string $error) : self
    {
        return new self($method, $error, 1102);
    }

    /**
     * @param string $method
     * @param string $error
     * @return StorageException
     */
    public static function writeError(string $method, string $error) : self
    {
        return new self($method, $error, 1103);
    }

    /**
     * @param string $method
     * @param string $error
     * @return StorageException
     */
    public static function flushError(string $method, string $error) : self
    {
        return new self($method, $error, 1104);
    }
}