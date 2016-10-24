<?php
declare(strict_types=1);

namespace Comely\IO\Cache\Engine;

use Comely\IO\Cache\Cache;
use Comely\IO\Cache\Exception\EngineException;

/**
 * Class Memcached
 * @package Comely\IO\Cache\Engine
 */
class Memcached implements EngineInterface
{
    /** @var Cache */
    private $cache;
    /** @var $memcached */
    private $memcached;

    /** @var string */
    private $host;
    /** @var int */
    private $port;

    /**
     * Memcached constructor.
     * @param Cache $cache
     * @param string $host
     * @param int $port
     * @throws EngineException
     */
    public function __construct(Cache $cache, string $host, int $port = 11211)
    {
        // Check if we have Memcached PECL
        if(!extension_loaded("memcached")) {
            throw EngineException::prerequisite(__CLASS__, 'Required extension/PECL "memcached" is not installed');
        }

        $this->cache  =   $cache;
        $this->memcached    =   new \Memcached();
        if(!$this->memcached->addServer($host, $port)) {
            throw EngineException::connectionError(
                __CLASS__,
                sprintf('Failed to add "%1$s:%2$d" as server', $host, $port)
            );
        }

        // Save host and port
        $this->host =   $host;
        $this->port =   $port;

        // Connected?
        if(!$this->isConnected()) {
            throw EngineException::connectionError(
                __CLASS__,
                sprintf('Failed to connect with MEMCACHED server on "%1$s:%2$d"', $host, $port)
            );
        }
    }

    /**
     * @return bool
     */
    public function isConnected() : bool
    {
        $port   =   !empty($this->port) ? $this->port : 11211;
        $host   =   sprintf('%s:%d', $this->host, $port);
        $servers    =   $this->memcached->getStats();
        if(array_key_exists($host, $servers)    &&  $servers[$host]["pid"]  >   0) {
            return true;
        }

        return false;
    }

    /**
     * @param string $key
     * @param $value
     * @param int $expire
     * @return bool
     * @throws EngineException
     */
    public function set(string $key, $value, int $expire = 0) : bool
    {
        $this->memcached->set($key, $value, $expire);
        if($this->memcached->getResultCode()    !== \Memcached::RES_SUCCESS) {
            throw EngineException::ioError(__METHOD__, $this->memcached->getResultMessage());
        }

        return true;
    }

    /**
     * @param string $key
     * @return bool
     * @throws EngineException
     */
    public function get(string $key)
    {
        $value  =   $this->memcached->get($key);
        if($value   === false) {
            // Check if get command failed OR stored value was in fact boolean false
            if($this->memcached->getResultCode()    !== \Memcached::RES_SUCCESS) {
                throw EngineException::ioError(__METHOD__, $this->memcached->getResultMessage());
            }
        }

        return false;
    }
}