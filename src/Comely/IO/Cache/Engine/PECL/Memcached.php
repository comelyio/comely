<?php
/**
 * This file is part of Comely IO package.
 * https://github.com/comelyio/comely
 *
 * Copyright (c) 2016-2017 Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/comelyio/comely/blob/master/LICENSE
 */

declare(strict_types=1);

namespace Comely\IO\Cache\Engine\PECL;

use Comely\IO\Cache\Cache;
use Comely\IO\Cache\Engine\EngineInterface;
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
        // \Memcached::OPT_BINARY_PROTOCOL is necessary for increment/decrement methods to work as expected
        $this->memcached->setOption(\Memcached::OPT_BINARY_PROTOCOL, true);
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
     * @return bool
     */
    public function disconnect() : bool
    {
        return $this->memcached->quit();
    }

    /**
     * @return EngineInterface
     */
    public function poke() : EngineInterface
    {
        // self::isConnected() method already checks that we're connected to server with pid check
        return $this;
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

        return $value;
    }

    /**
     * @param string $key
     * @param int $add
     * @return int
     * @throws EngineException
     */
    public function countUp(string $key, int $add = 1) : int
    {
        $value  =   $this->memcached->increment($key, $add, $add);
        if($value   === false   ||  !is_int($value)) {
            throw EngineException::ioError(__METHOD__, 'Increment operation failed on Memcached server');
        }

        return $value;
    }

    /**
     * @param string $key
     * @param int $sub
     * @return int
     * @throws EngineException
     */
    public function countDown(string $key, int $sub = 1) : int
    {
        $value  =   $this->memcached->decrement($key, $sub, $sub);
        if($value   === false   ||  !is_int($value)) {
            throw EngineException::ioError(__METHOD__, 'Decrement operation failed on Memcached server');
        }

        return $value;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has(string $key) : bool
    {
        $this->memcached->get($key);
        return $this->memcached->getResultCode()    === \Memcached::RES_NOTFOUND ? false : true;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function delete(string $key) : bool
    {
        $delete =   $this->memcached->delete($key);
        return $delete  === true ? true : false;
    }

    /**
     * @return bool
     */
    public function flush() : bool
    {
        if($this->memcached->flush()    === true) {
            return true;
        }

        return false;
    }
}