<?php
declare(strict_types=1);

namespace Comely\IO\Logger\Exception;

use Comely\IO\Logger\LoggerException;

/**
 * Class AlarmsException
 * @package Comely\IO\Logger\Exception
 */
class AlarmsException extends LoggerException
{
    /** @var string */
    protected static $componentId   =   "Comely\\IO\\Logger\\Alarms";

    /**
     * @param $function
     * @param int $level
     * @return AlarmsException
     */
    public static function alarmNotCallable($function, int $level) : self
    {
        return new self(
            self::$componentId,
            sprintf(
                'Failed to call alarm function "%1$s" on log level %2$d',
                is_array($function) ? sprintf('%1$s::%2$s', $function[0], $function[1]) : strval($function),
                $level
            ),
            1101
        );
    }
}