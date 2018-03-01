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

namespace Comely\IO\Cache;

use Comely\IO\Cache\Cacheable\CacheableInterface;
use Comely\IO\Cache\Cacheable\CacheItem;
use Comely\IO\Cache\Engine\EngineInterface;
use Comely\IO\Cache\Engine\PECL\Memcached;
use Comely\IO\Cache\Engine\Redis;
use Comely\IO\Cache\Exception\CacheException;
use Comely\IO\Cache\Exception\ConnectionException;
use Comely\IO\Cache\Exception\EngineException;
use Comely\Kernel\Extend\ComponentInterface;

/**
 * Class Cache
 * @package Comely\IO\Cache
 */
class Cache implements ComponentInterface
{
    private const CACHEABLE_IDENTIFIER = "~comely_CacheableObject-";
    private const CACHEABLE_MIN_LENGTH = 100;

    public const REDIS = 1001;
    public const MEMCACHED = 1002;

    /** @var string */
    private $cacheableId;
    /** @var int */
    private $cacheableIdLength;
    /** @var int */
    private $cacheableLengthFrom;
    /** @var array */
    private $servers;
    /** @var null|EngineInterface */
    private $engine;
    /** @var null|Indexing */
    private $index;

    /**
     * Cache constructor.
     * @throws CacheException
     */
    public function __construct()
    {
        $this->setCacheableIdentifier(self::CACHEABLE_IDENTIFIER, self::CACHEABLE_MIN_LENGTH);
        $this->servers = [];
        $this->engine = null;
        $this->index = null;
    }

    /**
     * Disconnect from cache server
     */
    public function __destruct()
    {
        $this->disconnect();
    }

    /**
     * Add a server to connection queue
     *
     * Multiple cache servers may be added before connection, if connection fails with first server then Comely
     * Cache component will try to connect to next server.
     *
     * @param int $engine
     * @param string $host
     * @param int $port
     * @return Server
     * @throws ConnectionException
     */
    public function addServer(int $engine, string $host = "127.0.0.1", int $port = 0): Server
    {
        $this->servers[] = new Server($engine, $host, $port);
        return end($this->servers);
    }

    /**
     * @return Cache
     * @throws \Comely\IO\Events\Exception\ListenerException
     */
    public function enableIndexing(): self
    {
        $this->index = new Indexing($this);
        return $this;
    }

    /**
     * Establish connection with cache server(s)
     *
     * This method will try to establish connection with servers in order they were added. Each failed connection
     * will trigger an error message (E_USER_WARNING), and if all of the connections fail then a ConnectionException
     * will be thrown.
     *
     * @throws ConnectionException
     */
    public function connect(): void
    {
        /** @var $server Server */
        $attempts = 0;
        foreach ($this->servers as $server) {
            try {
                $attempts++;
                if ($server->engine === self::REDIS) {
                    $engine = new Redis($server);
                } elseif ($server->engine === self::MEMCACHED) {
                    $engine = new Memcached($server);
                }

                // Reaching here means, connection successful!
                break;
            } catch (EngineException $e) {
                trigger_error($e->getMessage(), E_USER_WARNING);
            }
        }

        // Connection successful?
        if (isset($engine) && $engine instanceof EngineInterface) {
            $this->engine = $engine;
            return;
        }

        throw new ConnectionException('Failed to connect with cache server(s)', $attempts);
    }

    /**
     * Disconnect from currently connected cache server/engine
     */
    public function disconnect(): void
    {
        if ($this->engine) {
            $this->engine->disconnect();
        }

        $this->engine = null;
    }

    /**
     * Check connection status
     * @return bool
     */
    public function isConnected(): bool
    {
        if ($this->engine) {
            $connected = $this->engine->isConnected();
            if (!$connected) {
                $this->engine = null;
            }

            return $connected;
        }

        return false;
    }

    /**
     * Ping Cache Server
     *
     * This method will not only check the connection status, but also attempt to ping cache server. On failure,
     * cache component will be disconnected with cache server/engine. Optional $reconnect param. may be passed to
     * attempt reconnection on failure.
     *
     * @param bool $reconnect
     * @return bool
     * @throws ConnectionException
     * @throws EngineException
     */
    public function ping(bool $reconnect = false): bool
    {
        if ($this->engine) {
            $ping = $this->engine->ping();
            if (!$ping) {
                $this->engine = null;
                if ($reconnect) {
                    $this->connect();
                }
            }
        }

        return false;
    }

    /**
     * Store a key/value on cache engine/server
     *
     * Long strings, objects, arrays, NULL and booleans are first encoded as CacheItem and then stored serialized.
     *
     * @param string $key
     * @param $value
     * @param int $ttl
     * @return bool
     * @throws CacheException
     * @throws EngineException
     */
    public function set(string $key, $value, int $ttl = 0): bool
    {
        $this->checkConnection(__METHOD__);

        // Get type of value being stored
        $valueType = gettype($value);

        // Floats are evil, they should be converted to strings
        if ($valueType === "double") {
            $valueType = "string";
            $value = strval($value);
        }

        // Check value data type and proceed further...
        switch ($valueType) {
            case "string":
                if (strlen($value) >= $this->cacheableLengthFrom) {
                    $value = $this->encode(new CacheItem($key, $value, $ttl));
                }
                break;
            case "object":
            case "array":
                $value = $this->encode(new CacheItem($key, $value, $ttl));
                break;
            case "NULL":
            case "boolean":
                $value = $this->encode(new CacheItem($key, $value, $ttl));
                break;
            default:
                throw new CacheException(sprintf('Value of type "%s" cannot be stored on cache', $valueType));
        }

        $store = $this->engine->set($key, $value, $ttl);
        if ($store && $this->index) {
            $this->index->events()->trigger(Indexing::EVENT_ON_STORE)
                ->params($key, $valueType)
                ->fire();
        }

        return $store;
    }

    /**
     * Get value for provided key from cache engine/server
     *
     * This method will return NULL if key doesn't exist on cache server. An exception is thrown if there was an
     * error communicating with cache engine. If value is stored after encoding as CacheItem/CacheableInterface object
     * then the actual stored value will be returned, optionally param. $returnCacheable may be passed as TRUE
     * to get instance of CacheableInterface itself.
     *
     * @param string $key
     * @param bool $returnCacheable
     * @return CacheableInterface|mixed
     * @throws CacheException
     * @throws EngineException
     */
    public function get(string $key, bool $returnCacheable = false)
    {
        $this->checkConnection(__METHOD__);

        $value = $this->engine->get($key);
        if (is_string($value)) {
            $value = trim($value); // trim
            if (strlen($value) >= $this->cacheableLengthFrom) {
                if (substr($value, 0, $this->cacheableIdLength) === $this->cacheableId) {
                    $value = $this->decode($key, $value);
                    if (!$returnCacheable) {
                        $value = $value->yield();
                    }
                }
            } elseif (preg_match('/^\-?[0-9]+$/', $value)) {
                $value = intval($value); // Convert to integers
            }
        }

        return $value;
    }

    /**
     * Checks if a data exists on cache server corresponding to provided key
     *
     * @param string $key
     * @return bool
     * @throws CacheException
     * @throws EngineException
     */
    public function has(string $key): bool
    {
        $this->checkConnection(__METHOD__);
        return $this->engine->has($key);
    }

    /**
     * Delete an stored item with provided key on cache server
     *
     * @param string $key
     * @return bool
     * @throws CacheException
     * @throws EngineException
     */
    public function delete(string $key): bool
    {
        $this->checkConnection(__METHOD__);
        $delete = $this->engine->delete($key);
        if ($delete && $this->index) {
            $this->index->events()
                ->trigger(Indexing::EVENT_ON_DELETE)
                ->params($key)
                ->fire();
        }

        return $delete;
    }

    /**
     * Flushes all stored data from cache server
     *
     * @return bool
     * @throws CacheException
     * @throws EngineException
     */
    public function flush(): bool
    {
        $this->checkConnection(__METHOD__);
        $flush = $this->engine->flush();
        if ($flush && $this->index) {
            $this->index->events()->trigger(Indexing::EVENT_ON_FLUSH)
                ->fire();
        }

        return $flush;
    }

    /**
     * Increase stored integer value
     * If key doesn't already exist, a new key will be created with value 0 before increment
     *
     * @param string $key
     * @param int $inc
     * @return int
     * @throws CacheException
     * @throws EngineException
     */
    public function countUp(string $key, int $inc = 1): int
    {
        $this->checkConnection(__METHOD__);
        return $this->engine->countUp($key, $inc);
    }

    /**
     * Decrease stored integer value
     * If key doesn't already exist, a new key will be created with value 0 before decrement
     *
     * @param string $key
     * @param int $dec
     * @return int
     * @throws CacheException
     * @throws EngineException
     */
    public function countDown(string $key, int $dec = 1): int
    {
        $this->checkConnection(__METHOD__);
        return $this->engine->countDown($key, $dec);
    }

    /**
     * @param string $key
     * @param string $encoded
     * @return CacheableInterface
     * @throws CacheException
     */
    private function decode(string $key, string $encoded): CacheableInterface
    {
        // Decode from base64, trim from right (short strings are padded with null bytes), unserialize
        $cacheable = unserialize(rtrim(base64_decode($encoded)));
        if (!$cacheable instanceof CacheableInterface) {
            throw new CacheException(sprintf('Could not decode CacheableInterface object for key "%s"', $key));
        }

        return $cacheable;
    }

    /**
     * @param CacheableInterface $cacheable
     * @return string
     */
    private function encode(CacheableInterface $cacheable): string
    {
        $cacheable = serialize($cacheable);
        $padding = $this->cacheableLengthFrom - strlen($cacheable);
        if ($padding > 0) {
            $cacheable .= str_repeat("\0", $padding); // Pad with NULL-bytes
        }

        return base64_encode($cacheable);
    }

    /**
     * @param string $method
     * @throws CacheException
     */
    private function checkConnection(string $method): void
    {
        if (!$this->engine) {
            trigger_error(
                sprintf('Connection must be established before calling "%s" method', $method),
                E_USER_WARNING
            );

            throw new CacheException('Not connected to any cache engine/server');
        }
    }

    /**
     * Set custom identifier for objects extending CacheableInterface
     *
     * @param string|null $id
     * @param int|null $length
     * @return Cache
     * @throws CacheException
     */
    public function setCacheableIdentifier(string $id = null, int $length = null): self
    {
        if ($id) {
            if (!preg_match('/^[a-zA-Z0-9\_\-\~\.]{8,32}$/', $id)) {
                throw new CacheException('Invalid value passed to "setCacheableIdentifier" method');
            }

            $this->cacheableId = $id;
            $this->cacheableIdLength = strlen($id);
        }

        if ($length) {
            if ($length < 100 || $length <= $this->cacheableIdLength) {
                throw new CacheException('Length of encoded Cacheable objects must start from at least 100');
            }

            $this->cacheableLengthFrom = $length;
        }

        return $this;
    }
}