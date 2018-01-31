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
use Comely\IO\Cache\Server;

/**
 * Interface EngineInterface
 * @package Comely\IO\Cache\Kernel
 */
interface EngineInterface
{
    /**
     * EngineInterface constructor.
     * @param Server $server
     * @throws EngineException
     */
    public function __construct(Server $server);

    /**
     * @throws EngineException
     */
    public function connect(): void;

    /**
     * @return void
     */
    public function disconnect(): void;

    /**
     * @return bool
     */
    public function isConnected(): bool;

    /**
     * @return bool
     * @throws EngineException
     */
    public function ping(): bool;

    /**
     * @param string $key
     * @param $value
     * @param int $ttl
     * @return bool
     * @throws EngineException
     */
    public function set(string $key, $value, int $ttl): bool;

    /**
     * @param string $key
     * @return mixed
     * @throws EngineException
     */
    public function get(string $key);

    /**
     * @param string $key
     * @return bool
     * @throws EngineException
     */
    public function has(string $key): bool;

    /**
     * @param string $key
     * @return bool
     * @throws EngineException
     */
    public function delete(string $key): bool;

    /**
     * @return bool
     * @throws EngineException
     */
    public function flush(): bool;

    /**
     * @param string $key
     * @param int $inc
     * @return int
     * @throws EngineException
     */
    public function countUp(string $key, int $inc = 1): int;

    /**
     * @param string $key
     * @param int $dec
     * @return int
     * @throws EngineException
     */
    public function countDown(string $key, int $dec = 1): int;
}