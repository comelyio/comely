<?php
declare(strict_types=1);

namespace Comely\IO\Cache;

use Comely\IO\Cache\Engine\EngineInterface;
use Comely\IO\Cache\Engine\Memcached;
use Comely\IO\Cache\Engine\Redis;
use Comely\IO\Cache\Exception\EngineException;
use Comely\IO\Toolkit\Numbers;

/**
 * Class Cache
 * @package Comely\IO\Cache
 */
class Cache
{
    const ENGINE_DETERMINE  =   1;
    const ENGINE_MEMCACHED  =   2;
    const ENGINE_REDIS  =   4;
    const ENGINE_MEMCACHE   =   8;

    /** @var EngineInterface */
    private $engine;
    /** @var bool */
    private $silentMode;
    /** @var string */
    private $lastError;
    /** @var array */
    private $servers;
    /** @var int */
    private $stringEncodeLength;
    /** @var int */
    private $timeOut;

    /**
     * Cache constructor.
     */
    public function __construct()
    {
        $this->servers  =   [];
        $this->timeOut  =   1;
        $this->stringEncodeLength   =   100;
        $this->silentMode   =   true;
        $this->lastError    =   "";
    }

    /**
     * Disconnect
     */
    public function __destruct()
    {
        //$this->engine->disconnect();
    }

    /**
     * @param int $engine
     * @param string $host
     * @param int $port
     * @param int $priority
     * @return Cache
     * @throws CacheException
     */
    public function addServer(string $host, int $port = 0, int $priority = 1, int $engine = 1) : self
    {
        // Self determine cache engine?
        if($engine  === self::ENGINE_DETERMINE) {
            switch ($port) {
                case 11211: // Memcached
                    $engine =   self::ENGINE_MEMCACHED;
                    break;
                case 6379:
                    $engine =   self::ENGINE_REDIS;
                    break;
                case 0:
                    if(stripos($host, "memcached")  !== false) {
                        $engine =   self::ENGINE_MEMCACHED;
                    } elseif(stripos($host, "redis")    !== false) {
                        $engine =   self::ENGINE_REDIS;
                    } else {
                        $engine =   -1;
                    }

                    break;
                default:
                    $engine =   -1;
            }
        }

        // We have explicit cache engine to use?
        if(!in_array($engine, [self::ENGINE_MEMCACHED, self::ENGINE_REDIS])) {
            throw CacheException::badEngine();
        }

        // Append to servers array
        if(!isset($this->servers[$priority])) {
            $this->servers[$priority]   =   [];
        }

        $this->servers[$priority][] =   [
            "engine"    =>  $engine,
            "host"  =>  $host,
            "port"  =>  $port
        ];

        return $this;
    }

    /**
     * @return Cache
     * @throws \Exception
     */
    public function connect() : self
    {
        // Sort Servers
        ksort($this->servers, SORT_REGULAR);

        // Find and connect to a server
        $lastError  =   null;
        foreach($this->servers as $servers) {
            foreach($servers as $server) {
                try {
                    if($server["engine"]    === self::ENGINE_MEMCACHED) {
                        $this->engine   =   new Memcached($this, $server["host"], $server["port"]);
                    } elseif($server["engine"]  === self::ENGINE_REDIS) {
                        $this->engine   =   new Redis($this, $server["host"], $server["port"]);
                    }
                } catch (EngineException $e) {
                    $this->engine   =   null;
                    $lastError  =   $e;
                }
            }
        }

        // Connected?
        if(!$this->engine instanceof EngineInterface) {
            throw $lastError;
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isConnected() : bool
    {
        return $this->engine->isConnected();
    }

    /**
     * @return string
     */
    public function lastError() : string
    {
        return $this->lastError;
    }

    /**
     * Engine Operations
     */

    /**
     * @param string $key
     * @param $value
     * @param int $expire
     * @return bool
     * @throws CacheException
     */
    public function set(string $key, $value, int $expire = 0) : bool
    {
        try {
            $this->lastError    =   "";
            if(!$this->isConnected()) {
                throw CacheException::connectionNotEstablished(__METHOD__);
            }

            // Get type of value being stored
            $valueType  =   gettype($value);

            // Convert evil floats to String
            if($valueType   === "double") {
                $value  =   strval($value);
                $valueType  =   "string";
            }

            if(!in_array($valueType, ["boolean", "integer", "string", "array", "object", "NULL"])) {
                throw CacheException::unstorableType(__METHOD__, $key, $valueType);
            }

            switch ($valueType) {
                case "string":
                    if(strlen($value)   >=  $this->stringEncodeLength) {
                        $value  =   $this->encode($value);
                    }
                    break;
                case "array":
                case "object":
                    $value  =   $this->encode($value);
                    break;
                case "NULL":
                case "integer": // Todo: Change to IncrBy instead of encoding as Profile
                case "boolean":
                    if($this->engine instanceof Redis) {
                        $value  =   $this->encode($value);
                    }
                    break;
            }

            return $this->engine->set($key, $value, $expire);
        } catch (CacheException $e) {
            $this->lastError    =   $e->getMessage();
            if(!$this->silentMode) {
                throw $e;
            }

            return false;
        }
    }

    /**
     * @param string $key
     * @return mixed
     * @throws CacheException
     */
    public function get(string $key)
    {
        try {
            $this->lastError    =   "";
            if(!$this->isConnected()) {
                throw CacheException::connectionNotEstablished(__METHOD__);
            }

            $value  =   $this->engine->get($key);
            if(is_string($value)    &&  strlen($value)  >=  $this->stringEncodeLength) {
                $value  =   $this->decode($value);
            }

            return $value;
        } catch (CacheException $e) {
            $this->lastError    =   $e->getMessage();
            if(!$this->silentMode) {
                throw $e;
            }

            return false;
        }
    }

    /**
     * @return array
     * @throws CacheException
     */
    public function getAllKeys() : array
    {
        try {
            $this->lastError    =   "";
            if(!$this->isConnected()) {
                throw CacheException::connectionNotEstablished(__METHOD__);
            }

            // Todo: Implement

            return [];
        } catch (CacheException $e) {
            $this->lastError    =   $e->getMessage();
            if(!$this->silentMode) {
                throw $e;
            }

            return [];
        }
    }

    /**
     * Encoding/Decoding Profiles
     */

    /**
     * @param $subject string|array|object
     * @return string
     */
    private function encode($subject) : string
    {
        // Serialize passed object
        $profile    =   serialize(new Profile($subject));
        $padding    =   $this->stringEncodeLength-strlen($profile);
        if($padding >   0) {
            $profile    .=  str_repeat("\n", $padding); // Pad string with NULL-bytes
        }

        return base64_encode($profile);
    }

    /**
     * @param $string
     * @return mixed
     * @throws CacheException
     */
    private function decode($string)
    {
        $profile    =   @unserialize(rtrim(@base64_decode($string)));
        if(!$profile instanceof Profile) {
            throw CacheException::decodingError(__METHOD__, 'Failed to decode string');
        }

        try {
            $data   =   $profile->withdraw();
        } catch (\Exception $e) {
            throw CacheException::decodingError(
                __METHOD__,
                $e->getMessage()
            );
        }

        return $data;
    }

    /**
     * Configuration
     */

    /**
     * @param int $timeOut
     * @return Cache
     */
    public function setTimeout(int $timeOut = 1) : self
    {
        $this->timeOut  =   $timeOut;
        return $this;
    }

    /**
     * @return int
     */
    public function getTimeout() : int
    {
        return $this->timeOut;
    }

    /**
     * @param int $bytes
     * @return Cache
     * @throws CacheException
     */
    public function setStringEncodeLength(int $bytes = 100) : self
    {
        if(!Numbers::intRange($bytes, 64, PHP_INT_MAX)) {
            throw CacheException::stringEncodeLength();
        }

        $this->stringEncodeLength   =   $bytes;

        return $this;
    }

    /**
     * @param bool $trigger
     * @return Cache
     */
    public function silentMode(bool $trigger) : self
    {
        $this->silentMode   =   $trigger;
        return $this;
    }
}