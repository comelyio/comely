<?php
/**
 * This file is part of Comely IO package.
 * https://github.com/comelyio/comely
 *
 * Copyright (c) 2016-2018 Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/comelyio/comely/blob/master/LICENSE
 */

declare(strict_types=1);

namespace Comely\IO\Logger;

use Comely\IO\Logger\Alarms\Alarms;
use Comely\IO\Logger\Storage\StorageInterface;
use Comely\IO\Toolkit\Strings;

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
    /** @var bool */
    private $plainTextLogging;

    /**
     * Logger constructor.
     * @param StorageInterface $storage
     */
    public function __construct(StorageInterface $storage)
    {
        $this->storage  =   $storage;
        $this->alarms   =   new Alarms();
        $this->plainTextLogging =   false;
    }

    /**
     * @param int $flag
     * @param string $method
     * @return string
     * @throws LoggerException
     */
    private function flag2String(int $flag, string $method) : string
    {
        switch ($flag) {
            case self::DEBUG:
                return "debug";
            case self::INFO:
                return "info";
            case self::NOTICE:
                return "notice";
            case self::WARNING:
                return "warning";
            case self::CRITICAL:
                return "critical";
            default:
                throw LoggerException::invalidFlag($method);
        }
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

    /**
     * @param bool $switch
     * @return Logger
     */
    public function plainTextLogging(bool $switch) : self
    {
        $this->plainTextLogging =   $switch;
        return $this;
    }

    /**
     * @param Log $log
     * @return bool
     */
    public function write(Log $log) : bool
    {
        $type   =   $this->flag2String($log->getLevel(), __METHOD__);
        $name   =   $log->getName();

        // Create payload
        if(!$this->plainTextLogging) {
            $payload[]  =   '#comely-logger|format:serialized';
            $payload[]  =   serialize($log);
        } else {
            $boundary   =   str_repeat("=\0", 5);
            $payload[]  =   '#comely-logger|format:plain';
            $payload[]  =   $boundary;
            $payload[]  =   sprintf('Name: %s', $name);
            $payload[]  =   sprintf('Type/Level: %s', strtoupper($type));
            $payload[]  =   sprintf('Time: %s', date('d M Y h:i A', $log->getTimeStamp()));
            $payload[]  =   $boundary;
            $payload[]  =   $log->getMessage();
            $payload[]  =   $boundary;
            $payload[]  =   "Attached data:";
            $payload[]  =   print_r($log->getAttachedData(), true);
        }

        // Write to Storage
        $this->storage->write($type, $name, implode("\n", $payload));
        return true;
    }

    /**
     * @param int $type
     * @param string $name
     * @param string $message
     * @param array|null $data
     * @param bool $write
     * @return Log|bool
     */
    private function log(int $type, string $name, string $message, array $data = null, bool $write = true)
    {
        $this->flag2String($type, __METHOD__); // Check flag

        // Create new Log
        $log    =   (new Log(
            sprintf(
                '%s_%d',
                Strings::filter(strtolower($name), "ln", false, "-_"),
                time()
            ),
            $type
        ))->append($message);

        if(is_array($data)) {
            $log->attachData($data);
        }

        if(!$write) {
            return $log;
        }

        return $this->write($log);
    }

    /**
     * @param string $name
     * @param string $message
     * @param array|null $data
     * @param bool $write
     * @return log
     */
    public function logDebug(string $name, string $message, array $data = null, bool $write = true)
    {
        return $this->log(self::DEBUG, $name, $message, $data, $write);
    }

    /**
     * @param string $name
     * @param string $message
     * @param array|null $data
     * @param bool $write
     * @return bool|Log
     */
    public function logInfo(string $name, string $message, array $data = null, bool $write = true)
    {
        return $this->log(self::INFO, $name, $message, $data, $write);
    }

    /**
     * @param string $name
     * @param string $message
     * @param array|null $data
     * @param bool $write
     * @return bool|Log
     */
    public function logNotice(string $name, string $message, array $data = null, bool $write = true)
    {
        return $this->log(self::NOTICE, $name, $message, $data, $write);
    }

    /**
     * @param string $name
     * @param string $message
     * @param array|null $data
     * @param bool $write
     * @return bool|Log
     */
    public function logWarning(string $name, string $message, array $data = null, bool $write = true)
    {
        return $this->log(self::WARNING, $name, $message, $data, $write);
    }

    /**
     * @param string $name
     * @param string $message
     * @param array|null $data
     * @param bool $write
     * @return bool|Log
     */
    public function logCritical(string $name, string $message, array $data = null, bool $write = true)
    {
        return $this->log(self::CRITICAL, $name, $message, $data, $write);
    }
}