<?php
/**
 * This file is part of Comely IO package.
 * https://github.com/comelyio/comely
 *
 * Copyright (c) 2016-2018 Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/comelyio/comely/blob/master/LICENSE
 */

declare(strict_types=1);

namespace Comely\IO\DependencyInjection\Exception;

use Comely\IO\DependencyInjection\DependencyInjectionException;

/**
 * Class ContainerException
 * @package Comely\IO\DependencyInjection\Exception
 */
class ContainerException extends DependencyInjectionException
{
    /** @var string */
    protected static $componentId   =   "Comely\\IO\\DependencyInjection\\Container";

    /**
     * @param string $method
     * @param string $key
     * @return ContainerException
     */
    public static function keyExists(string $method, string $key) : self
    {
        return new self($method, sprintf('Key "%1$s" already exists in container', $key), 1201);
    }

    /**
     * @param string $method
     * @param string $type
     * @return ContainerException
     */
    public static function badService(string $method, string $type) : self
    {
        return new self(
            $method,
            sprintf(
                'Second argument must be an Instance of an Object or a full class name as String, "%1$s" given',
                $type
            ),
            1202
        );
    }

    /**
     * @param string $method
     * @param string $class
     * @return ContainerException
     */
    public static function classNotFound(string $method, string $class) : self
    {
        return new self($method, sprintf('Class "%1$s" not found', $class), 1203);
    }

    /**
     * @param string $method
     * @param string $key
     * @return ContainerException
     */
    public static function serviceNotFound(string $method, string $key) : self
    {
        return new self($method, sprintf('Service "%1$s" not found', $key), 1204);
    }

    /**
     * @param string $method
     * @param string $class
     * @param string $setter
     * @param string $key
     * @return ContainerException
     */
    public static function injectMethodNotFound(string $method, string $class, string $setter, string $key) : self
    {
        return new self(
            $method,
            sprintf(
                'Cannot inject "1$s". Setter method "%2$s" not found or not public in class "%2$s"',
                $key,
                $setter,
                $class
            ),
            1205
        );
    }

    /**
     * @param string $method
     * @param string $class
     * @param string $prop
     * @param string $key
     * @return ContainerException
     */
    public static function injectPropertyNotFound(string $method, string $class, string $prop, string $key) : self
    {
        return new self(
            $method,
            sprintf(
                'Cannot inject "1$s". Property "%2$s" not found or not public in class "%2$s"',
                $key,
                $prop,
                $class
            ),
            1206
        );
    }
}