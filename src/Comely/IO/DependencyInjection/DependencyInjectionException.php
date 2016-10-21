<?php
declare(strict_types=1);

namespace Comely\IO\DependencyInjection;

/**
 * Class DependencyInjectionException
 * @package Comely\IO\DependencyInjection
 */
class DependencyInjectionException extends \ComelyException
{
    /** @var string */
    protected static $componentId   =   __NAMESPACE__;

    /**
     * @return DependencyInjectionException
     */
    public static function serializeContainer() : self
    {
        return new self(self::$componentId, "DI containers cannot be Un-serialized", 1001);
    }

    /**
     * @return DependencyInjectionException
     */
    public static function cloneContainer() : self
    {
        return new self(self::$componentId, "DI containers cannot be cloned", 1002);
    }
}