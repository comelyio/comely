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

namespace Comely\IO\Logger\Exception;

use Comely\IO\Logger\LoggerException;

/**
 * Class StorageException
 * @package Comely\IO\Logger\Exception
 */
class StorageException extends LoggerException
{
    protected static $componentId   =   "Comely\\IO\\Logger\\Storage";

    /**
     * @return StorageException
     */
    public static function diskPrivileges() : self
    {
        return new self(
            self::$componentId,
            'Disk instance must have read+write privileges',
            1301
        );
    }

    /**
     * @param string $method
     * @param string $message
     * @return StorageException
     */
    public static function writeError(string $method, string $message) : self
    {
        return new self(self::$componentId, sprintf('%s: %s', $method, $message), 1302);
    }
}