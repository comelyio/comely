<?php
/**
 * This file is part of Comely IO package.
 * https://github.com/comelyio/comely
 *
 * Copyright (c) 2016-2017 Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/comelyio/comely/blob/master/LICENSE
 */

declare(strict_types=1);

namespace Comely\IO\Http\Exception;

use Comely\IO\Http\HttpException;

/**
 * Class RouterException
 * @package Comely\IO\Http\Exception
 */
class RouterException extends HttpException
{
    /** @var string */
    protected static $componentId   =   "Comely\\IO\\Http\\Router";

    /**
     * @param string $path
     * @return RouterException
     */
    public static function badControllersPath(string $path) : self
    {
        return new self(
            self::$componentId,
            sprintf('"%1$s" is not a valid path to controllers directory', $path),
            1201
        );
    }

    /**
     * @return RouterException
     */
    public static function controllersPathNull() : self
    {
        return new self(self::$componentId, 'Path to controllers directory is not set', 1202);
    }

    /**
     * @param string $method
     * @param string $path
     * @return RouterException
     */
    public static function badRoutingPath(string $method, string $path) : self
    {
        return new self($method, sprintf('Bad path "%1$s" cannot be routed', $path), 1203);
    }

    /**
     * @param string $controller
     * @return RouterException
     */
    public static function controllerNotFound(string $controller) : self
    {
        return new self(self::$componentId, sprintf('Controller class "%1$s" not found', $controller), 1204);
    }

    /**
     * @param string $controller
     * @param string $error
     * @return RouterException
     */
    public static function controllerInitFail(string $controller, string $error) : self
    {
        return new self(
            self::$componentId,
            sprintf(
                'Failed to construct controller "%1$s" error "%2$s"',
                $controller,
                $error
            ),
            1205
        );
    }

    /**
     * @param string $controller
     * @return RouterException
     */
    public static function badController(string $controller) : self
    {
        return new self(
            self::$componentId,
            sprintf(
                'Controller "%1$s" must implement "Comely\\IO\\Http\\ControllersInterface"',
                $controller
            ),
            1206
        );
    }
}