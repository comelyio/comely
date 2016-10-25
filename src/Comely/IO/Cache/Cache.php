<?php
declare(strict_types=1);

namespace Comely\IO\Cache;

use Comely\IO\Cache\Engine\EngineInterface;
use Comely\IO\Cache\Engine\PECL\Memcached;
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
    const ENGINE_REDIS  =   2;
    const ENGINE_MEMCACHED  =   8; // Alias to PECL\Memcached
    const ENGINE_PECL_MEMCACHED =   8;


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
        $this->engine->disconnect();
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
     * Check if still connected to cache engine/server
     *
     * This method determines if connection with cache engine/server "seems" alive. This method will not throw
     * any exception and returns boolean TRUE or FALSE. It is NOT as reliable as self::poke() method.
     *
     * Example:
     * On REDIS server this method will check "timed_out" key from stream's metadata.
     * On (PECL) Memcached this method will check "pid" from getStats method.
     *
     * @return bool
     */
    public function isConnected() : bool
    {
        return $this->engine->isConnected();
    }

    /**
     * Poke cache engine/server
     *
     * This method goes an extra mile to check connection status (as opposed to self::isConnected() method).
     *
     * Example:
     * On REDIS self::isConnected() will check "timed_out" key from stream's metadata to determine if connection
     * is still alive but self::poke() method first internally calls self::isConnected() method and then sends
     * "PING" command to REDIS server expecting a valid response.
     *
     * @param bool $reconnect
     * @return bool
     * @throws CacheException
     * @throws EngineException
     */
    public function poke(bool $reconnect = false) : bool
    {
        if(!$this->isConnected()) {
            throw CacheException::connectionNotEstablished(__METHOD__);
        }

        try {
            $this->engine   =   $this->engine->poke();
        } catch (EngineException $e) {
            if($reconnect) {
                $this->connect();
                return true;
            } else {
                throw $e;
            }
        }

        return true;
    }

    /**
     * Retrieve error message associated with last command or an empty String
     * Included commands: set|get|countUp|countDown|has|delete|flush
     *
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
     * Save a key/value on cache engine
     *
     * If value is a String that exceeds "stringEncodeLength" in size, this will be encoded before its saved.
     * Encoding of plain string adds a little overhead which can be avoided by setting an appropriate amount for
     * "stringEncodeLength" that is neither too big nor too small using "setStringEncodeLength" method. Default value
     * for "stringEncodeLength" prop. is 100 bytes.
     *
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
     * Retrieve a value with key from cache engine
     *
     * If key does not exist on cache engine, this method will return NULL (silent mode) or throw an exception
     * If a key has a string value comprised of all digits, it will be returned as an Integer.
     * If a key has a string value that exceeds "stringEncodeLength" in size, will be considered as encoded profile,
     * and decoded before being returned.
     *
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
            if(is_string($value)) {
                $value  =   trim($value);
                if(strlen($value)  >=  $this->stringEncodeLength) {
                    $value  =   $this->decode($value); // Decode Profile
                } elseif(preg_match('/^\-?[0-9]+$/', $value)) {
                    $value  =   intval($value); // Integer
                }
            }

            return $value;
        } catch (CacheException $e) {
            $this->lastError    =   $e->getMessage();
            if(!$this->silentMode) {
                throw $e;
            }

            return null;
        }
    }

    /**
     * Increase value of stored integer
     *
     * @param string $key
     * @param int $add
     * @return int|bool
     * @throws CacheException
     */
    public function countUp(string $key, int $add = 1)
    {
        try {
            $this->lastError    =   "";
            if(!$this->isConnected()) {
                throw CacheException::connectionNotEstablished(__METHOD__);
            }

            $value  =   $this->engine->countUp($key, $add);
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
     * Decrease value of stored integer
     *
     * If value after decrement is lower than 0, value will be set to 0. It will never return or set value to negative
     * integer (even if cache engine supports it) for consistency across all engines.
     *
     * @param string $key
     * @param int $sub
     * @return bool|int
     * @throws CacheException
     */
    public function countDown(string $key, int $sub = 1)
    {
        try {
            $this->lastError    =   "";
            if(!$this->isConnected()) {
                throw CacheException::connectionNotEstablished(__METHOD__);
            }

            $value  =   $this->engine->countDown($key, $sub);
            if($value   <=  0) {
                $this->engine->set($key, 0);
                $value  =   0;
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
     * Check if key exists on cache engine
     *
     * If key is not found on cache engine/server, no exception will be thrown and boolean FALSE will be returned.
     *
     * @param string $key
     * @return bool
     * @throws CacheException
     */
    public function has(string $key) : bool
    {
        try {
            $this->lastError    =   "";
            if(!$this->isConnected()) {
                throw CacheException::connectionNotEstablished(__METHOD__);
            }

            $value  =   $this->engine->has($key);
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
     * Deletes a key/value from cache engine
     *
     * If key is not found on cache engine/server, no exception will be thrown and boolean FALSE will be returned.
     *
     * @param string $key
     * @return bool
     * @throws CacheException
     */
    public function delete(string $key) : bool
    {
        try {
            $this->lastError    =   "";
            if(!$this->isConnected()) {
                throw CacheException::connectionNotEstablished(__METHOD__);
            }

            $value  =   $this->engine->delete($key);
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
     * Flush all key/value pairs (clear memory)
     *
     * @return bool
     * @throws CacheException
     */
    public function flush() : bool
    {
        try {
            $this->lastError    =   "";
            if(!$this->isConnected()) {
                throw CacheException::connectionNotEstablished(__METHOD__);
            }

            $value  =   $this->engine->flush();
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