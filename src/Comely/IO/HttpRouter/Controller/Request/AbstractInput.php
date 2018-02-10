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
     */
    public function __construct()
    {
        $this->data = [];
        $this->count = 0;
        $this->index = 0;
    }

    /**
     * @param string $key
     * @param $value
     */
    public function append(string $key, $value)
    {
        $this->data[$key] = $value;
        $this->count++;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return $this->count;
    }

    /**
     * @return void
     */
    public function rewind(): void
    {
        reset($this->data);
    }

    /**
     * @return mixed
     */
    public function current()
    {
        return current($this->data);
    }

    /**
     * @return string
     */
    public function key(): string
    {
        return key($this->data);
    }

    /**
     * @return void
     */
    public function next(): void
    {
        next($this->data);
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        return is_null(key($this->data)) ? false : true;
    }
}