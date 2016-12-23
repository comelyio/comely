<?php
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
}