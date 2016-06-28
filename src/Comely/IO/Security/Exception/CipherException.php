<?php
declare(strict_types=1);

namespace Comely\IO\Security\Exception;

use Comely\IO\Security\SecurityException;

/**
 * Class CipherException
 * @package Comely\IO\Security\Exception
 */
class CipherException extends SecurityException
{
    protected static $componentId   =   "Comely\\IO\\Security\\Cipher";

    /**
     * @return CipherException
     */
    public static function initError() : self
    {
        return new self(
            self::$componentId,
            'Cipher component requires PHP "OpenSSL" extension and "hash" functions',
            1101
        );
    }

    /**
     * @param string $cipher
     * @return CipherException
     */
    public static function badCipherMethod(string $cipher) : self
    {
        return new self(
            self::$componentId,
            sprintf(
                'Cipher method "%1$s" is not supported by OpenSSL',
                $cipher
            ),
            1102
        );
    }

    /**
     * @param string $algo
     * @return CipherException
     */
    public static function badHashAlgo(string $algo) : self
    {
        return new self(
            self::$componentId,
            sprintf(
                'Hashing algorithm "%1$s" not found',
                $algo
            ),
            1103
        );
    }

    /**
     * @param string $type
     * @return CipherException
     */
    public static function badDataType(string $type) : self
    {
        return new self(self::$componentId, sprintf('Data type "%1$s" is not supported', $type) , 1104);
    }

    /**
     * @param string $method
     * @param int $bits
     * @return CipherException
     */
    public static function badKey(string $method, int $bits) : self
    {
        return new self(
            $method,
            sprintf(
                'Expecting %1$d-bit hexadecimal string for $secret parameter',
                $bits
            ),
            1105
        );
    }
}