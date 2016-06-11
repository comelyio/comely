<?php
declare(strict_types=1);

namespace Comely\Kernel\Exception;

/**
 * Class SessionException
 * @package Comely\Kernel\Exception
 */
class SessionException extends HttpException
{
    protected static $componentId   =   "Comely\\Kernel\\Http\\Session";

    /**
     * @return SessionException
     */
    public static function badStorage() : SessionException
    {
        return new self(self::$componentId, "Unacceptable session storage", 1101);
    }

    /**
     * @param string $storage
     * @param string $error
     * @return SessionException
     */
    public static function storageError(string $storage, string $error) : SessionException
    {
        return new self(self::$componentId, sprintf('%1$s', $error, $storage), 1102);
    }

    /**
     * @param string $key
     * @param string $error
     * @return SessionException
     */
    public static function configError(string $key, string $error) : SessionException
    {
        return new self(self::$componentId, sprintf('Unacceptable setting for "%1$s": %2$s', $key, $error), 1103);
    }
}