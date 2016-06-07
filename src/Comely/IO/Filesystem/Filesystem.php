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
}