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

namespace Comely\IO\Cache\Engine;

use Comely\IO\Cache\Exception\EngineException;
use Comely\IO\Cache\Exception\RedisException;
use Comely\IO\Cache\Server;

/**
 * Class Redis
 * @package Comely\IO\Cache\Engine
 */
class Redis implements EngineInterface
{
    public const ENGINE = "Redis";

    /** @var Server */
    private $server;
    /** @var null|resource */
    private $sock;

    /**
     * Redis constructor.
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
        // Establish connection
        $errorNum = 0;
        $errorMsg = "";
        $socket = stream_socket_client(
            sprintf('%s:%d', $this->server->host, $this->server->port),
            $errorNum,
            $errorMsg,
            $this->server->getTimeout()
        );

        // Connected?
        if (!is_resource($socket)) {
            throw new EngineException(self::ENGINE, sprintf('Connection error: %s', $errorMsg), $errorNum);
        }

        $this->sock = $socket;
        stream_set_timeout($this->sock, $this->server->getTimeout());
    }

    /**
     * @return void
     */
    public function disconnect(): void
    {
        if ($this->sock) {
            try {
                $this->send("QUIT");
            } catch (EngineException $e) {
                trigger_error($e->getMessage(), E_USER_WARNING);
            }
        }

        $this->sock = null;
    }

    /**
     * @return bool
     */
    public function isConnected(): bool
    {
        if ($this->sock) {
            $timedOut = @stream_get_meta_data($this->sock)["timed_out"] ?? true;
            if ($timedOut) {
                $this->sock = null;
                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * @return bool
     * @throws RedisException
     */
    public function ping(): bool
    {
        // Check if connected
        if (!$this->isConnected()) {
            throw new RedisException('Lost connection with server');
        }

        $ping = $this->send("PING");
        if (!is_string($ping) || strtolower($ping) !== "pong") {
            throw new RedisException('Lost connection with server');
        }

        return true;
    }

    /**
     * @param string $key
     * @param $value
     * @param int $ttl
     * @return bool
     * @throws EngineException
     * @throws RedisException
     */
    public function set(string $key, $value, int $ttl = 0): bool
    {
        $query = $ttl > 0 ?
            sprintf('SETEX %s %d "%s"', $key, $ttl, $value) :
            sprintf('SET %s "%s"', $key, $value);

        $exec = $this->send($query);
        if ($exec !== "OK") {
            throw EngineException::errorStore(self::ENGINE, $key);
        }

        return true;
    }

    /**
     * @param string $key
     * @return mixed
     * @throws EngineException
     */
    public function get(string $key): mixed
    {
        try {
            return $this->send(sprintf('GET %s', $key));
        } catch (RedisException $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);
            throw EngineException::errorFetch(self::ENGINE, $key);
        }
    }

    /**
     * @param string $key
     * @return bool
     * @throws EngineException
     */
    public function has(string $key): bool
    {
        try {
            return $this->send(sprintf('EXISTS %s', $key)) === 1 ? true : false;
        } catch (RedisException $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);
            throw EngineException::errorHas(self::ENGINE, $key);
        }
    }

    /**
     * @param string $key
     * @return bool
     * @throws EngineException
     */
    public function delete(string $key): bool
    {
        try {
            return $this->send(sprintf('DEL %s', $key)) === 1 ? true : false;
        } catch (RedisException $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);
            throw EngineException::errorDelete(self::ENGINE, $key);
        }
    }

    /**
     * @return bool
     * @throws EngineException
     */
    public function flush(): bool
    {
        try {
            return $this->send('FLUSHALL');
        } catch (RedisException $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);
            throw EngineException::errorFlush(self::ENGINE);
        }
    }

    /**
     * @param string $key
     * @param int $inc
     * @return int
     * @throws EngineException
     */
    public function countUp(string $key, int $inc = 1): int
    {
        try {
            $count = $this->send(sprintf('INCRBY %s %d', $key, $inc));
        } catch (RedisException $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);
        }

        if (isset($count) && is_int($count)) {
            return $count;
        }

        throw EngineException::errorCount(self::ENGINE, $key, "increment");
    }

    /**
     * @param string $key
     * @param int $dec
     * @return int
     * @throws EngineException
     */
    public function countDown(string $key, int $dec = 1): int
    {
        try {
            $count = $this->send(sprintf('DECRBY %s %d', $key, $dec));
        } catch (RedisException $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);
        }

        if (isset($count) && is_int($count)) {
            return $count;
        }

        throw EngineException::errorCount(self::ENGINE, $key, "decrement");
    }

    /**
     * @param string $command
     * @return mixed
     * @throws RedisException
     */
    private function send(string $command): mixed
    {
        if (!$this->sock) {
            throw new RedisException('Not connected to any server');
        }

        $command = trim($command);
        if (strtolower($command) == "disconnect") {
            return @fclose($this->sock);
        }

        $write = fwrite($this->sock, $this->command($command));
        if ($write === false) {
            throw new RedisException(sprintf('Failed to "%1$s" command', explode(" ", $command)[0]));
        }

        return $this->response();
    }

    /**
     * @param string $command
     * @return string
     */
    private function command(string $command): string
    {
        $parts = str_getcsv($command, " ", '"');
        $prepared = "*" . count($parts) . "\r\n";
        foreach ($parts as $part) {
            $prepared .= "$" . strlen($part) . "\r\n" . $part . "\r\n";
        }

        return $prepared;
    }

    /**
     * @return mixed
     * @throws RedisException
     */
    private function response(): mixed
    {
        // Get response from stream
        $response = fgets($this->sock);
        if (!is_string($response)) {
            throw new RedisException('Failed to receive response from server');
        }

        // Prepare response for parsing
        $response = trim($response);
        $responseType = substr($response, 0, 1);
        $data = substr($response, 1);

        // Check response
        switch ($responseType) {
            case "-": // Error
                throw new RedisException(substr($data, 4));
                break;
            case "+": // Simple String
                return $data;
            case ":": // Integer
                return intval($data);
            case "$": // Bulk String
                $bytes = intval($data);
                if ($bytes > 0) {
                    $data = stream_get_contents($this->sock, $bytes + 2);
                    if (!is_string($data)) {
                        throw new RedisException('Failed to read bulk-string response');
                    }

                    return trim($data); // Return trimmed
                } elseif ($bytes === 0) {
                    return ""; // Empty String
                } elseif ($bytes === -1) {
                    return null; // NULL
                } else {
                    throw new RedisException('Unexpected number of bytes to read');
                }
        }

        throw new RedisException('Unexpected response from server');
    }
}