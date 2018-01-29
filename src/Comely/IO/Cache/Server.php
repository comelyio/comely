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

use Comely\IO\Cache\Exception\ConnectionException;

/**
 * Class Server
 * @package Comely\IO\Cache
 */
class Server
{
    private const ENGINES = [
        Cache::REDIS,
        Cache::MEMCACHED
    ];

    /** @var int */
    public $engine;
    /** @var string */
    public $host;
    /** @var int */
    public $port;
    /** @var int */
    private $timeOut;

    /**
     * Server constructor.
     * @param int $engine
     * @param string $host
     * @param int $port
     * @throws ConnectionException
     */
    public function __construct(int $engine, string $host = "127.0.0.1", int $port = 0)
    {
        if (!in_array($engine, self::ENGINES)) {
            throw new ConnectionException('Failed to add server. Unknown cache server type.');
        }

        $this->engine = $engine;
        $this->host = $host;
        $this->port = $port;
        $this->timeOut = 0;

        // Set known default ports if param not passed
        if (!$this->port) {
            switch ($this->engine) {
                case Cache::REDIS:
                    $this->port = 6379;
                    break;
                case Cache::MEMCACHED:
                    $this->port = 11211;
                    break;
            }
        }
    }

    /**
     * @param int $seconds
     * @return Server
     */
    public function setTimeout(int $seconds): self
    {
        $this->timeOut = $seconds;
        return $this;
    }

    /**
     * @return int
     */
    public function getTimeout(): int
    {
        return $this->timeOut;
    }
}