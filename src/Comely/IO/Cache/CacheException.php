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

namespace Comely\IO\Cache;

/**
 * Class CacheException
 * @package Comely\IO\Cache
 */
class CacheException extends \ComelyException
{
    /** @var string */
    protected static $componentId   =   __NAMESPACE__;

    /**
     * @return CacheException
     */
    public static function badEngine() : self
    {
        return new self(self::$componentId, 'Provide a valid cache ENGINE_* flag', 1001);
    }

    /**
     * @param string $method
     * @return CacheException
     */
    public static function connectionNotEstablished(string $method) : self
    {
        return new self($method, 'Connection not established with cache engine', 1002);
    }

    /**
     * @return CacheException
     */
    public static function stringEncodeLength() : self
    {
        return new self(
            self::$componentId,
            'Configuration value for "stringEncodeLength" must be between 64 and PHP_INT_MAX',
            1003
        );
    }

    /**
     * @param string $method
     * @param string $error
     * @return CacheException
     */
    public static function decodingError(string $method, string $error) : self
    {
        return new self($method, $error, 1004);
    }

    /**
     * @param string $method
     * @param string $key
     * @param string $type
     * @return CacheException
     */
    public static function unstorableType(string $method, string $key, string $type) : self
    {
        return new self($method, sprintf('Cannot store value of type "%2$s" for key "%1$s"', $key, $type), 1005);
    }
}