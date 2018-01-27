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

namespace Comely\IO\Session\Exception;

use Comely\IO\Session\SessionException;

/**
 * Class StorageException
 * @package Comely\IO\Logger\Exception
 */
class StorageException extends SessionException
{
    /** @var string */
    protected static $componentId   =   "Comely\\IO\\Session\\Storage";

    /**
     * @return StorageException
     */
    public static function diskPrivileges() : self
    {
        return new self(self::$componentId, 'Disk instance does not have read+write privileges', 1101);
    }

    /**
     * @param string $method
     * @param string $error
     * @return StorageException
     */
    public static function readError(string $method, string $error) : self
    {
        return new self($method, $error, 1102);
    }

    /**
     * @param string $method
     * @param string $error
     * @return StorageException
     */
    public static function writeError(string $method, string $error) : self
    {
        return new self($method, $error, 1103);
    }

    /**
     * @param string $method
     * @param string $error
     * @return StorageException
     */
    public static function deleteError(string $method, string $error) : self
    {
        return new self($method, $error, 1104);
    }

    /**
     * @param string $method
     * @param string $error
     * @return StorageException
     */
    public static function flushError(string $method, string $error) : self
    {
        return new self($method, $error, 1105);
    }
}