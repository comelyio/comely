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

namespace Comely\IO\Events;

/**
 * Class Trigger
 * @package Comely\IO\Events
 */
class Trigger
{
    /** @var Event */
    private $event;
    /** @var null|array */
    private $params;

    /**
     * Trigger constructor.
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
    }

    /**
     * @param array ...$params
     * @return Trigger
     */
    public function params(...$params): self
    {
        $this->params = $params;
        array_push($this->params, $this->event);
        return $this;
    }

    /**
     * @return int
     */
    public function fire(): int
    {
        $listeners = $this->event->listeners();
        if (!$listeners->count()) { // Have listeners?
            return 0; // No listeners
        }

        $count = 0;
        foreach ($listeners as $i => $callback) {
            call_user_func_array($callback, $this->params ?? []);
            $count++;
        }

        return $count;
    }
}