<?php
declare(strict_types=1);

namespace Comely\IO\Logger;

use Comely\IO\Logger\Alarms\Alarms;
use Comely\IO\Logger\Storage\StorageInterface;

/**
 * Class Logger
 * @package Comely\IO\Logger
 */
class Logger
{
    /** Lowest level, use for general debugging */
    const DEBUG =   1;
    /** Informative log */
    const INFO  =   2;
    /** Notice, an alarm may or may not be necessary */
    const NOTICE    =   4;
    /** Warning, something bad is expected to happen, an alarm should be raised */
    const WARNING   =   8;
    /** Critical, an exceptional alarm should be set here */
    const CRITICAL  =   16;

    /** @var StorageInterface */
    private $storage;
    /** @var Alarms */
    private $alarms;

    /**
     * Logger constructor.
     * @param StorageInterface $storage
     */
    public function __construct(StorageInterface $storage)
    {
        $this->storage  =   $storage;
        $this->alarms   =   new Alarms();
    }

    /**
     * @param callable $function
     * @param int $levelFrom
     * @return Logger
     */
    public function setAlarm(callable $function, int $levelFrom) : self
    {
        $this->alarms->add($levelFrom, $function);
        return $this;
    }
}