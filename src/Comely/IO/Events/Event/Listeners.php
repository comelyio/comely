<?php
/**
 * This file is part of Comely package.
 * https://github.com/comelyio/comely
 *
 * Copyright (c) 2016-2019 Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/comelyio/comely/blob/master/LICENSE
 */

declare(strict_types=1);

namespace Comely\IO\Events\Event;

use Comely\IO\Events\Event;
use Comely\IO\Events\Exception\ListenerException;

/**
 * Class Listeners
 * @package Comely\IO\Events\Event
 */
class Listeners implements \Countable, \Iterator
{
    /** @var Event */
    private $event;
    /** @var array */
    private $callbacks;
    /** @var int */
    private $count;
    /** @var int */
    private $index;

    /**
     * Listeners constructor.
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
        $this->callbacks = [];
        $this->count = 0;
        $this->index = 0;
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
        $this->index = 0;
    }

    /**
     * @return callable
     */
    public function current(): callable
    {
        return $this->callbacks[$this->index];
    }

    /**
     * @return int
     */
    public function key(): int
    {
        return $this->index;
    }

    /**
     * @return void
     */
    public function next(): void
    {
        ++$this->index;
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        return isset($this->callbacks[$this->index]);
    }

    /**
     * @param callable $callback
     * @return bool
     * @throws ListenerException
     */
    public function append(callable $callback): bool
    {
        if (!is_callable($callback)) {
            throw new ListenerException(
                sprintf('Listener to event "%s" is not a valid callback', $this->event->tag())
            );
        }

        $this->callbacks[] = $callback;
        $this->count++;

        return true;
    }
}