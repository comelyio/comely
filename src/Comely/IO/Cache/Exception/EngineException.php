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

namespace Comely\IO\Cache\Exception;

use Comely\IO\Cache\CacheException;

/**
 * Class EngineException
 * @package Comely\IO\Cache\Exception
 */
class EngineException extends CacheException
{
    /** @var string */
    protected static $componentId   =   "Comely\\IO\\Cache\\Engine\\EngineInterface";

    /**
     * @param string $engine
     * @param string $message
     * @return EngineException
     */
    public static function prerequisite(string $engine, string $message) : self
    {
        return new self($engine, $message, 1101);
    }

    /**
     * @param string $engine
     * @param string $error
     * @return EngineException
     */
    public static function connectionError(string $engine, string $error) : self
    {
        return new self($engine, $error, 1102);
    }

    /**
     * @param string $engine
     * @param string $error
     * @return EngineException
     */
    public static function ioError(string $engine, string $error) :  self
    {
        return new self($engine, $error, 1103);
    }
}