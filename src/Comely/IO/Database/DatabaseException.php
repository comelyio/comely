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

namespace Comely\IO\Database;

/**
 * Class DatabaseException
 * @package Comely\IO\Database
 */
class DatabaseException extends \ComelyException
{
    /** @var string */
    protected static $componentId   =   __NAMESPACE__;

    /**
     * @param string $message
     * @return DatabaseException
     */
    public static function connectionError(string $message) : self
    {
        return new self(self::$componentId, $message, 1001);
    }

    /**
     * @param string $method
     * @param string $message
     * @return DatabaseException
     */
    public static function queryError(string $method, string $message) : self
    {
        return new self($method, $message, 1002);
    }

    /**
     * @param string $message
     * @return DatabaseException
     */
    public static function pdoError(string $message) : self
    {
        return new self(self::$componentId, $message, 1003);
    }
}