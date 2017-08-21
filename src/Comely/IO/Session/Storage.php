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

namespace Comely\IO\Session;

use Comely\IO\Database\Database;
use Comely\IO\Database\Schema;
use Comely\IO\Session\Storage\Cache;
use Comely\IO\Session\Storage\Disk;
use Comely\IO\Session\Storage\StorageInterface;

/**
 * Class Storage
 * @package Comely\IO\Session
 */
class Storage
{
    /**
     * @param Database $db
     * @return StorageInterface
     */
    public static function Database(Database $db) : StorageInterface
    {
        Schema::loadTable($db, 'Comely\IO\Session\Storage\Database');
        return Schema::table("comely_sessions");
    }

    /**
     * @param \Comely\IO\Filesystem\Disk $disk
     * @return StorageInterface
     */
    public static function Disk(\Comely\IO\Filesystem\Disk $disk) : StorageInterface
    {
        return new Disk($disk);
    }

    /**
     * @param \Comely\IO\Filesystem\Disk $disk
     * @return StorageInterface
     */
    public static function Filesystem(\Comely\IO\Filesystem\Disk $disk) : StorageInterface
    {
        return self::Disk($disk);
    }

    /**
     * @param \Comely\IO\Cache\Cache $cache
     * @return StorageInterface
     */
    public static function Cache(\Comely\IO\Cache\Cache $cache) : StorageInterface
    {
        return new Cache($cache);
    }
}