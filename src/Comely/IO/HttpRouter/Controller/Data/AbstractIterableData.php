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

use Comely\IO\HttpRouter\Router;

/**
 * Class AbstractIterableData
 * @package Comely\IO\HttpRouter\Controller\Data
 */
abstract class AbstractIterableData implements \Countable, \Iterator
{
    /** @var Router */
    protected $router;
    /** @var array|null */
    private $data;
    /** @var int */
    private $count;

    /**
     * AbstractIterableData constructor.
     * @param Router $router
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
        $this->data = [];
        $this->count = 0;
    }

    /**
     * @param Property $prop
     * @return bool
     */
    final protected function setProp(Property $prop): bool
    {
        $this->data[strtolower($prop->key)] = $prop;
        $this->count++;
        return true;
    }

    /**
     * @param string $key
     * @return Property|null
     */
    final protected function getProp(string $key): ?Property
    {
        return $this->data[strtolower($key)] ?? null;
    }

    /**
     * @return array
     */
    final public function array(): array
    {
        $array = [];
        /** @var Property $prop */
        foreach ($this->data as $key => $prop) {
            $array[$prop->key] = $prop->value;
        }

        return $array;
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