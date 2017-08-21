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

namespace Comely\IO\Filesystem\Exception;

use Comely\IO\Filesystem\FilesystemException;

/**
 * Class DiskException
 * @package Comely\IO\Filesystem\Exception
 */
class DiskException extends FilesystemException
{
    /** @var string */
    protected static $componentId   =   "Comely\\IO\\Filesystem\\Disk";

    /**
     * @param string $message
     * @return DiskException
     */
    public static function diskInit(string $message) : self
    {
        return new self(self::$componentId, $message, 1101);
    }

    /**
     * @param string $method
     * @param string $message
     * @return DiskException
     */
    public static function invalidPath(string $method, string $message) : self
    {
        return new self($method, $message, 1102);
    }

    /**
     * @param string $method
     * @return DiskException
     */
    public static function readError(string $method) : self
    {
        return new self($method, "Disk instance doesn't have reading privilege", 1103);
    }

    /**
     * @param string $method
     * @return DiskException
     */
    public static function writeError(string $method) : self
    {
        return new self($method, "Disk instance doesn't have writing privilege", 1104);
    }

    /**
     * @param string $method
     * @param string $message
     * @return DiskException
     */
    public static function fsError(string $method, string $message) : self
    {
        return new self($method, $message, 1105);
    }
}