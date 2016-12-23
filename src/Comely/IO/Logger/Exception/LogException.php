<?php
declare(strict_types=1);

namespace Comely\IO\Logger\Exception;

use Comely\IO\Logger\LoggerException;

/**
 * Class LogException
 * @package Comely\IO\Logger\Exception
 */
class LogException extends LoggerException
{
    /** @var string */
    protected static $componentId   =   "Comely\\IO\\Logger\\Log";
}