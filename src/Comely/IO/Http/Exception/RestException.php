<?php
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