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
     * @param string $key
     * @param string $error
     * @return SessionException
     */
    public static function configError(string $key, string $error) : self
    {
        return new self(
            self::$componentId,
            sprintf('Unacceptable setting for "%1$s": %2$s', $key, $error),
            1001
        );
    }

    /**
     * @param string $method
     * @return SessionException
     */
    public static function sessionNotExists(string $method) : self
    {
        return new self($method, 'ComelySession instance is not found', 1002);
    }

    /**
     * @return SessionException
     */
    public static function sessionExists() : self
    {
        return new self(self::$componentId, "Session was already started", 1003);
    }

    /**
     * @return SessionException
     */
    public static function badWakeUp() : self
    {
        return new self(self::$componentId, "Read session data is corrupt", 1006);
    }
}