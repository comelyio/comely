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

namespace Comely\IO\Filesystem;

/**
 * Class Filesystem
 * @package Comely\IO\Filesystem
 */
class Filesystem
{
    /**
     * Get an instance of Disk
     *
     * @param string $path
     * @return Disk
     */
    public static function disk(string $path = ".") : Disk
    {
        static::clearStatCache();
        return new Disk($path);
    }

    /**
     * Call clearstatcache() with $clear_realpath_cache = true
     */
    public static function clearStatCache()
    {
        clearstatcache(true);
    }

    /**
     * @param string $content
     * @return string
     */
    public static function prependUtf8Bom(string $content) : string
    {
        return pack("CCC", 0xef, 0xbb, 0xbf) . $content;
    }

    /**
     * @param string $content
     * @return string
     */
    public static function removeUtf8Bom(string $content) : string
    {
        return preg_replace("/^" . pack("H*", "EFBBBF") . "/", "", $content);
    }
}