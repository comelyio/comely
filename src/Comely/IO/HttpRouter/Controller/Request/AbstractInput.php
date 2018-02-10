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

namespace Comely\IO\HttpRouter\Controller\Request;

/**
 * Class AbstractInput
 * @package Comely\IO\HttpRouter\Controller\Request
 */
abstract class AbstractInput implements \Countable, \Iterator
{
    /** @var array */
    protected $data;
    /** @var int */
    private $count;
    /** @var int */
    private $index;

    /**
     * AbstractInput constructor.
     * @param array $data
     */
    public function __construct(?array $data = null)
    {
        $this->data = [];
        $this->count = 0;
        $this->index = 0;

        if ($data) {
            $this->data($data);
        }
    }

    /**
     * @param array $data
     */
    final protected function data(array $data): void
    {
        $this->data = $data;
        $this->count = count($data);
    }

    /**
     * @param string $key
     * @param $value
     */
    final protected function append(string $key, $value): void
    {
        $this->data[$key]   =   $value;
        $this->count++;
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    protected function get(string $key)
    {
        return $this->data[$key] ?? null;
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
        return current($this->data);
    }

    /**
     * @return string
     */
    final public function key(): string
    {
        return key($this->data);
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