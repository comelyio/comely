<?php
/**
 * This file is part of Comely package.
 * https://github.com/comelyio/comely
 *
 * Copyright (c) 2016-2018 Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/comelyio/comely/blob/master/LICENSE
 */

declare(strict_types=1);

namespace Comely\IO\Cache\Engine\PECL;

use Comely\IO\Cache\Engine\EngineInterface;
use Comely\IO\Cache\Exception\EngineException;
use Comely\IO\Cache\Server;

/**
 * Class Memcached
 * @package Comely\IO\Cache\Kernel\PECL
 */
class Memcached implements EngineInterface
{
    public const ENGINE = "MEMCACHED";

    /** @var Server */
    private $server;
    /** @var null|\Memcached */
    private $memcached;

    /**
     * Memcached constructor.
     * @param Server $server
     * @throws EngineException
     */
    public function __construct(Server $server)
    {
        $this->server = $server;
        $this->connect();
    }

    /**
     * @throws EngineException
     */
    public function connect(): void
    {
        if (!extension_loaded("memcached")) {
            throw new EngineException(self::ENGINE, 'Required PECL extension "memcached" is not installed');
        }

        $this->memcached = new \Memcached();
        // \Memcached::OPT_BINARY_PROTOCOL is necessary for increment/decrement methods to work as expected
        $this->memcached->setOption(\Memcached::OPT_BINARY_PROTOCOL, true);
        if (!$this->memcached->addServer($this->server->host, $this->server->port)) {
            throw new EngineException(
                self::ENGINE,
                sprintf('Failed to add "%1$s:%2$d" as server', $this->server->host, $this->server->port)
            );
        }

        // Connected?
        if (!$this->isConnected()) {
            throw new EngineException(
                self::ENGINE,
                sprintf('Failed to connect with "%1$s:%2$d" server', $this->server->host, $this->server->port)
            );
        }
    }

    /**
     * @return void
     */
    public function disconnect(): void
    {
        try {
            $this->checkConnection();
        } catch (EngineException $e) {
            return;
        }

        $this->memcached->quit();
    }

    /**
     * @return bool
     */
    public function isConnected(): bool
    {
        try {
            $this->checkConnection();
        } catch (EngineException $e) {
            return false;
        }

        $stats = $this->memcached->getStats();
        $server = sprintf('%s:%d', $this->server->host, $this->server->port);
        $pid = intval($stats[$server]["pid"] ?? 0);
        return $pid ? true : false;
    }

    /**
     * @return bool
     * @throws EngineException
     */
    public function ping(): bool
    {
        $connection = $this->isConnected(); // PID check is apparently sufficient
        if (!$connection) {
            throw new EngineException(self::ENGINE, 'Lost connection with server');
        }

        return true;
    }

    /**
     * @param string $key
     * @param $value
     * @param int $ttl
     * @return bool
     * @throws EngineException
     */
    public function set(string $key, $value, int $ttl): bool
    {
        $this->checkConnection();
        $this->memcached->set($key, $value, $ttl);
        if ($this->memcached->getResultCode() !== \Memcached::RES_SUCCESS) {
            throw EngineException::errorStore(self::ENGINE, $key);
        }

        return true;
    }

    /**
     * @param string $key
     * @return mixed
     * @throws EngineException
     */
    public function get(string $key)
    {
        $this->checkConnection();
        $val = $this->memcached->get($key);
        if (false === $val) {
            // Check if get command failed, OR bool(false) was stored as val
            if ($this->memcached->getResultCode() !== \Memcached::RES_SUCCESS) {
                throw EngineException::errorFetch(self::ENGINE, $key);
            }
        }

        return $val;
    }

    /**
     * @param string $key
     * @return bool
     * @throws EngineException
     */
    public function has(string $key): bool
    {
        $this->checkConnection();
        $this->memcached->get($key);
        return $this->memcached->getResultCode() === \Memcached::RES_NOTFOUND ? false : true;
    }

    /**
     * @param string $key
     * @return bool
     * @throws EngineException
     */
    public function delete(string $key): bool
    {
        $this->checkConnection();
        return $this->memcached->delete($key);
    }

    /**
     * @return bool
     * @throws EngineException
     */
    public function flush(): bool
    {
        $this->checkConnection();
        return $this->memcached->flush();
    }

    /**
     * @param string $key
     * @param int $inc
     * @return int
     * @throws EngineException
     */
    public function countUp(string $key, int $inc = 1): int
    {
        $count = $this->memcached->increment($key, $inc, 0);
        if (!is_int($count)) {
            throw EngineException::errorCount(self::ENGINE, $key, "increment");
        }

        return $count;
    }

    /**
     * @param string $key
     * @param int $dec
     * @return int
     * @throws EngineException
     */
    public function countDown(string $key, int $dec = 1): int
    {
        $count = $this->memcached->decrement($key, $dec, 0);
        if (!is_int($count)) {
            throw EngineException::errorCount(self::ENGINE, $key, "increment");
        }

        return $count;
    }

    /**
     * @throws EngineException
     */
    private function checkConnection()
    {
        if (!$this->memcached) {
            throw new EngineException(self::ENGINE, 'Connection not established');
        }
    }
}