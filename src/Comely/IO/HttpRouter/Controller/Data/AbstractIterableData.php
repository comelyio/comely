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

namespace Comely\IO\HttpRouter\Controller\Data;

/**
 * Class AbstractIterableData
 * @package Comely\IO\HttpRouter\Controller\Data
 */
abstract class AbstractIterableData implements \Countable, \Iterator
{
    /** @var array|null */
    private $data;
    /** @var int */
    private $count;

    /**
     * AbstractData constructor.
     * @param array|null $data
     */
    final public function __construct(?array $data = null)
    {
        $this->data = [];
        $this->count = 0;

        if (is_array($data)) {
            $this->feed($data);
        }
    }

    /**
     * @param array $data
     */
    final protected function feed(array $data): void
    {
        foreach ($data as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * @param string $key
     * @param $value
     * @return bool
     */
    protected function set(string $key, $value): bool
    {
        if (!preg_match('/^[a-zA-Z0-9\_\-\.]+$/', $key)) {
            return false;
        }

        $this->data[strtolower($key)] = new Property($key, $value);
        $this->count++;
        return true;
    }

    /**
     * @param string $key
     * @return Property|null
     */
    protected function get(string $key): ?Property
    {
        return $this->data[strtolower($key)] ?? null;
    }

    /**
     * @return int
     */
    final public function count(): int
    {
        return $this->count;
    }

    /**
     * @return void
     */
    final public function rewind(): void
    {
        reset($this->data);
    }

    /**
     * @return mixed
     */
    final public function current()
    {
        /** @var Property $prop */
        $prop = current($this->data);
        return $prop->value;
    }

    /**
     * @return string
     */
    final public function key(): string
    {
        /** @var Property $prop */
        $prop = $this->data[key($this->data)];
        return $prop->key;
    }

    /**
     * @return void
     */
    final public function next(): void
    {
        next($this->data);
    }

    /**
     * @return bool
     */
    final public function valid(): bool
    {
        return is_null(key($this->data)) ? false : true;
    }
}