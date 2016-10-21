<?php
declare(strict_types=1);

namespace Comely\IO\Http;

/**
 * Class HttpException
 * @package Comely\IO\Http
 */
class HttpException extends \ComelyException
{
    /** @var string */
    protected static $componentId   =   __NAMESPACE__;
}