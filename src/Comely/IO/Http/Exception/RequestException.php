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

namespace Comely\IO\Http\Exception;

use Comely\IO\Http\HttpException;

/**
 * Class RequestException
 * @package Comely\IO\Http\Exception
 */
class RequestException extends HttpException
{
    /** @var string */
    protected static $componentId   =   "Comely\\IO\\Http\\Request";

    /**
     * @param string $method
     * @return RequestException
     */
    public static function badMethod(string $method) : self
    {
        return new self(
            self::$componentId,
            sprintf('Request method "%1$s" is not acceptable', strtoupper($method)),
            1101
        );
    }

    /**
     * @param string $method
     * @return RequestException
     */
    public static function sendHeaderLocation(string $method) : self
    {
        return new self(
            $method,
            'Location header cannot be sent using this method. Use "redirect" method instead',
            1102
        );
    }

    /**
     * @param string $method
     * @param string $key
     * @param string $type
     * @return RequestException
     */
    public static function setBadData(string $method, string $key, string $type) : self
    {
        return new self($method, sprintf('Cannot store value type "%2$s" for key "%1$s"', $key, $type), 1103);
    }
}