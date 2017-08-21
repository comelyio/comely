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
 * Class RestException
 * @package Comely\IO\Http\Exception
 */
class RestException extends HttpException
{
    /** @var string */
    protected static $componentId   =   "Comely\\IO\\Http\\REST";

    /**
     * @param string $httpMethod
     * @param string $contentType
     * @return RestException
     */
    public static function badInputContentMethod(string $httpMethod, string $contentType) : self
    {
        return new self(
            self::$componentId,
            sprintf(
                'Http method "%1$s" cannot accept input content type "%2$s"',
                $httpMethod,
                $contentType
            ),
            1301
        );
    }

    /**
     * @param string $httpMethod
     * @param string $contentType
     * @return RestException
     */
    public static function getInputDataFailed(string $httpMethod, string $contentType) : self
    {
        return new self(
            self::$componentId,
            sprintf(
                'Failed to parse input stream type "%2$s" via "%1$s" method',
                $httpMethod,
                $contentType
            ),
            1301
        );
    }
}