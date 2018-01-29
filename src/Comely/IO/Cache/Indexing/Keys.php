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

namespace Comely\IO\Cache\Indexing;

/**
 * Class Keys
 * @package Comely\IO\Cache\Indexing
 */
class Keys implements \Countable, \Iterator, \Serializable
{
    /** @var array */
    private $keys;
    /** @var int */
    private $count;
    /** @var int */
    private $position;

    /**
     * Keys constructor.
     */
    public function __construct()
    {
        $this->keys = [];
        $this->count = 0;
        $this->position = 0;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return $this->count;
    }

    /**
     * @return string
     */
    public function serialize(): string
    {
        return serialize([$this->keys]);
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        $this->keys = unserialize($serialized);
        $this->count = count($this->keys);
        $this->position = 0;
    }

    /**
     * @return void
     */
    public function rewind(): void
    {
        reset($this->keys);
    }

    /**
     * @return string
     */
    public function current(): string
    {
        return current($this->keys);
    }

    /**
     * @return string
     */
    public function key(): string
    {
        return key($this->keys);
    }

    /**
     * @return void
     */
    public function next(): void
    {
        next($this->keys);
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        return is_string(key($this->keys));
    }

    /**
     * @param string $key
     * @param string $dataType
     */
    public function append(string $key, string $dataType): void
    {
        $this->keys[$key] = $dataType;
    }

    /**
     * @param string $key
     */
    public function delete(string $key): void
    {
        unset($this->keys[$key]);
    }

    /**
     * @return void
     */
    public function flush(): void
    {
        $this->keys = [];
    }
}