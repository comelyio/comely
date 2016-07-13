<?php
declare(strict_types=1);

namespace Comely\IO\DependencyInjection;

/**
 * Class AbstractDI
 * @package Comely\IO\DependencyInjection
 */
abstract class AbstractDI
{
    /**
     * @throws DependencyInjectionException
     */
    final public function __wakeup()
    {
        throw DependencyInjectionException::serializeContainer();
    }

    /**
     * @throws DependencyInjectionException
     */
    final public function __clone()
    {
        throw DependencyInjectionException::cloneContainer();
    }
}