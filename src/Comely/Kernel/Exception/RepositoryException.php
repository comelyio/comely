<?php
declare(strict_types=1);

namespace Comely\Kernel\Exception;

use Comely\Kernel\KernelException;

/**
 * Class RepositoryException
 * @package Comely\Kernel\Exception
 */
class RepositoryException extends KernelException
{
    protected static $componentId   =   "Comely\\Kernel\\Repository";

    /**
     * @return RepositoryException
     */
    public static function badKeyName() : RepositoryException
    {
        return new self(self::$componentId, "Key must be an alphabetic String", 1101);
    }

    /**
     * @param string $key
     * @return RepositoryException
     */
    public static function cannotOverrideKey(string $key) : RepositoryException
    {
        return new self(self::$componentId, sprintf('Cannot override key "%1$s"', $key), 1102);
    }

    /**
     * @return RepositoryException
     */
    public static function badCallReference() : RepositoryException
    {
        return new self(self::$componentId, "This method should be called directly from Repository", 1103);
    }

    /**
     * @return RepositoryException
     */
    public static function badInstance() : RepositoryException
    {
        return new self(self::$componentId, "Second argument must be an instance of some object", 1104);
    }

    /**
     * @param string $key
     * @return RepositoryException
     */
    public static function instanceNotFound(string $key) : RepositoryException
    {
        return new self(self::$componentId, sprintf('No instance was found in Repository for "%1$s"', $key), 1105);
    }
}