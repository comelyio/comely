<?php
declare(strict_types=1);

namespace Comely\IO\DependencyInjection\Exception;

use Comely\IO\DependencyInjection\DependencyInjectionException;

/**
 * Class RepositoryException
 * @package Comely\IO\DependencyInjection\Exception
 */
class RepositoryException extends DependencyInjectionException
{
    protected static $componentId   =   "Comely\\IO\\DependencyInjection\\Repository";

    /**
     * @return RepositoryException
     */
    public static function badInstance() : self
    {
        return new self(self::$componentId, "Only instances can be stored", 1101);
    }

    /**
     * @param string $key
     * @return RepositoryException
     */
    public static function instanceNotFound(string $key) : self
    {
        return new self(
            self::$componentId,
            sprintf('Instance not found with key "%1$s"', $key),
            1102
        );
    }
}