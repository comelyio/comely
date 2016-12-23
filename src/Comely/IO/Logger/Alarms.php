<?php
declare(strict_types=1);

namespace Comely\IO\Logger\Alarms;
use Comely\IO\Logger\Exception\AlarmsException;
use Comely\IO\Logger\Log;

/**
 * Class Alarms
 * @package Comely\IO\Logger\Alarms
 */
class Alarms
{
    /** @var array */
    private $alarms;

    /**
     * Alarms constructor.
     */
    public function __construct()
    {
        $this->alarms   =   [];
    }

    /**
     * @param int $level
     * @param callable $callback
     */
    public function add(int $level, callable $callback)
    {
        $this->alarms   =   [
            "level" =>  $level,
            "function"  =>  $callback
        ];
    }

    /**
     * @param int $level
     * @param Log $log
     * @return mixed
     * @throws AlarmsException
     */
    public function call(int $level, Log $log)
    {
        $function   =   null;
        foreach($this->alarms as $alarm) {
            if($level   >=  $alarm["level"]) {
                $function   =   $alarm["function"];
            }
        }

        if(!is_callable($function, false)) {
            throw AlarmsException::alarmNotCallable($function, $level);
        }

        return call_user_func_array($function, [$log]);
    }
}