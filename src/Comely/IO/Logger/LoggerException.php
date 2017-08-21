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

namespace Comely\IO\Logger;

/**
 * Class LoggerException
 * @package Comely\IO\Logger
 */
class LoggerException extends \ComelyException
{
    /** @var string */
    protected static $componentId   =   __NAMESPACE__;

    /**
     * @param string $method
     * @return LoggerException
     */
    public static function invalidFlag(string $method) : self
    {
        return new self(
            self::$componentId,
            sprintf('Method "%1$s" was provided with an invalid flag', $method),
            1001
        );
    }
}