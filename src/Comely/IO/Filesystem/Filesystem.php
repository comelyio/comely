<?php
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