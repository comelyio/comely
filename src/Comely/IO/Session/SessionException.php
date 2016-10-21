<?php
declare(strict_types=1);

namespace Comely\IO\Session;

/**
 * Class SessionException
 * @package Comely\IO\Session
 */
class SessionException extends \ComelyException
{
    /** @var string */
    protected static $componentId   =   __NAMESPACE__;

    /**
     * @return SessionException
     */
    public static function badStorage() : self
    {
        return new self(self::$componentId, "Unacceptable session storage", 1001);
    }

    /**
     * @param string $storage
     * @param string $error
     * @return SessionException
     */
    public static function storageError(string $storage, string $error) : self
    {
        return new self(self::$componentId, sprintf('%1$s', $error, $storage), 1002);
    }

    /**
     * @param string $key
     * @param string $error
     * @return SessionException
     */
    public static function configError(string $key, string $error) : self
    {
        return new self(self::$componentId, sprintf('Unacceptable setting for "%1$s": %2$s', $key, $error), 1003);
    }

    /**
     * @param string $message
     * @return SessionException
     */
    public static function readError(string $message) : self
    {
        return new self(self::$componentId, $message, 1004);
    }

    /**
     * @param string $message
     * @return SessionException
     */
    public static function writeError(string $message) : self
    {
        return new self(self::$componentId, $message, 1005);
    }

    /**
     * @return SessionException
     */
    public static function badWakeUp() : self
    {
        return new self(self::$componentId, "Read session data is corrupt", 1006);
    }

    /**
     * @return SessionException
     */
    public static function sessionAlreadyStarted() : self
    {
        return new self(self::$componentId, "Session was already started", 1007);
    }
}