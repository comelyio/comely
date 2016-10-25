<?php
declare(strict_types=1);

namespace Comely\IO\Cache\Engine;

use Comely\IO\Cache\Cache;

/**
 * Interface EngineInterface
 * @package Comely\IO\Cache\Engine
 */
interface EngineInterface
{
    /**
     * @return bool
     */
    public function isConnected() : bool;

    /**
     * @return EngineInterface
     */
    public function poke() : self;

    /**
     * @return bool
     */
    public function disconnect() : bool;

    /**
     * @param string $key
     * @param $value
     * @param int $expire
     * @return bool
     */
    public function set(string $key, $value, int $expire = 0) : bool;

    /**
     * @param string $key
     * @return mixed
     */
    public function get(string $key);

    /**
     * @param string $key
     * @return bool
     */
    public function has(string $key) : bool;

    /**
     * @param string $key
     * @param int $add
     * @return int
     */
    public function countUp(string $key, int $add = 1) : int;

    /**
     * @param string $key
     * @param int $sub
     * @return int
     */
    public function countDown(string $key, int $sub = 1) : int;

    /**
     * @param string $key
     * @return bool
     */
    public function delete(string $key) : bool;

    /**
     * @return bool
     */
    public function flush() : bool;
}