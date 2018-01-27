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

namespace Comely\IO\Cache\Engine;

use Comely\IO\Cache\Cache;
use Comely\IO\Cache\Exception\EngineException;

/**
 * Class Redis
 * @package Comely\IO\Cache\Engine
 */
class Redis implements EngineInterface
{
    /** @var Cache */
    private $cache;
    /** @var resource */
    private $socket;
    /** @var string */
    private $host;
    /** @var int */
    private $port;

    /**
     * Redis constructor.
     * @param Cache $cache
     * @param string $host
     * @param int $port
     */
    public function __construct(Cache $cache, string $host, int $port = 6379)
    {
        $this->cache    =   $cache;
        $this->host =   $host;
        $this->port =   $port;

        $this->connect(); // Establish connection
    }

    /**
     * Revive existing connection or establish a new one
     */
    public function connect()
    {
        // Check if already connected
        if(!$this->isConnected()) {
            // Establish connection
            $errorNum   =   0;
            $errorMsg   =   "";
            $redis  =   @stream_socket_client(
                sprintf('%s:%d', $this->host, $this->port),
                $errorNum,
                $errorMsg,
                $this->cache->getTimeout()
            );

            // Do we have stream (resource) ?
            if(!$redis) {
                throw EngineException::connectionError(
                    __CLASS__,
                    sprintf('Redis connection error[%1$d]: %2$s', $errorNum, $errorMsg)
                );
            } else {
                $this->socket   =   $redis;
                @stream_set_timeout($this->socket, $this->cache->getTimeout());
            }
        }
    }

    /**
     * @return bool
     */
    public function isConnected() : bool
    {
        if($this->socket) {
            $timedOut   =   @stream_get_meta_data($this->socket)["timed_out"] ?? true;
            if($timedOut) {
                $this->socket   =   null;
                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function disconnect() : bool
    {
        if(is_resource($this->socket)) {
            $this->redisCommand("QUIT");
            return true;
        }

        return false;
    }

    /**
     * @return EngineInterface
     * @throws EngineException
     */
    public function poke() : EngineInterface
    {
        $ping   =   $this->redisCommand("PING");
        if(!is_string($ping)    ||  strtolower($ping)   !== "pong") {
            throw EngineException::connectionError(__CLASS__, 'Lost connection with Redis server');
        }

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
        $query  =   $expire >   0 ?
            sprintf('SETEX %s %d "%s"', $key, $expire, $value) :
            sprintf('SET %s "%s"', $key, $value);

        $exec   =   $this->redisCommand($query);
        if($exec    !== "OK") {
            throw EngineException::ioError(__CLASS__, 'Failed to set value at Redis server');
        }

        return true;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function get(string $key)
    {
        $value  =   $this->redisCommand(sprintf('GET %s', $key));
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
        $exec   =   $this->redisCommand(sprintf('INCRBY %s %d', $key, $add));
        if(!is_int($exec)) {
            throw EngineException::ioError(__CLASS__, 'Command "INCRBY" failed');
        }

        return $exec;
    }

    /**
     * @param string $key
     * @param int $sub
     * @return int
     * @throws EngineException
     */
    public function countDown(string $key, int $sub = 1) : int
    {
        $exec   =   $this->redisCommand(sprintf('DECRBY %s %d', $key, $sub));
        if(!is_int($exec)) {
            throw EngineException::ioError(__CLASS__, 'Command "DECRBY" failed');
        }

        return $exec;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has(string $key) : bool
    {
        return $this->redisCommand(sprintf('EXISTS %s', $key))  === 1 ? true : false;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function delete(string $key) : bool
    {
        $delete =   $this->redisCommand(sprintf('DEL %s', $key));
        return $delete  === 1 ?  true : false;
    }

    /**
     * @return bool
     */
    public function flush() : bool
    {
        $this->redisCommand('FLUSHALL');
        return true;
    }

    /**
     * Redis API
     * http://redis.io/topics/protocol
     */

    /**
     * @param string $command
     * @return bool
     * @throws EngineException
     */
    private function redisCommand(string $command)
    {
        $command	=	trim($command);
        if(strtolower($command)	==	"disconnect")
            return @fclose($this->socket);

        $write  =	fwrite($this->socket, $this->redisPrepare($command));
        if($write   ===	false) {
            throw EngineException::ioError(__CLASS__, 'Failed to send command to Redis server');
        } else {
            return $this->redisResponse();
        }
    }

    /**
     * @param string $command
     * @return string
     */
    private function redisPrepare(string $command) : string
    {
        $parts	=	str_getcsv($command, " ", '"');
        $prepared	=	"*" . count($parts) . "\r\n";
        foreach($parts as $part) {
            $prepared	.=	"$" . strlen($part) . "\r\n" . $part . "\r\n";
        }

        return $prepared;
    }

    /**
     * @return int|null|string
     * @throws EngineException
     */
    private function redisResponse()
    {
        // Get response from stream
        $response   =   fgets($this->socket);
        if($response    === false) {
            throw EngineException::ioError(
                __CLASS__,
                'Failed to receive response from Redis server'
            );
        }

        // Prepare response for parsing
        $response   =   trim($response);
        $responseType   =   substr($response, 0, 1);
        $data   =   substr($response, 1);

        // Check response
        switch ($responseType) {
            case "-": // Error
                throw EngineException::ioError(__CLASS__, substr($data, 4));
                break;
            case "+": // Simple String
                return $data;
            case ":": // Integer
                return intval($data);
            case "$": // Bulk String
                $bytes  =   intval($data);
                if($bytes   >   0) {
                    $data   =   @stream_get_contents($this->socket, $bytes+2);
                    if($data    === false) {
                        throw EngineException::ioError(__CLASS__, 'Failed to read bulk-string response');
                    }

                    return trim($data);
                } elseif($bytes === 0) {
                    return ""; // Empty String
                } elseif($bytes === -1) {
                    return null; // NULL
                } else {
                    throw EngineException::ioError(__CLASS__, 'Unexpected number of bytes to read');
                }
                break;
        }

        // Unexpected response from server?
        throw EngineException::ioError(__CLASS__, 'Unexpected response from server');
    }
}