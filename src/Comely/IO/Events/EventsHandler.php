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

use Comely\Engine\Extend\ComponentInterface;
use Comely\IO\Events\Event\Trigger;

/**
 * Class EventsHandler
 * @package Comely\IO\Events
 */
class EventsHandler implements ComponentInterface
{
    /** @var array */
    private $events;

    /**
     * EventsHandler constructor.
     */
    public function __construct()
    {
        $this->events = [];
    }

    /**
     * Returns a registered Event if exists, otherwise creates a new Event
     *
     * @param string $tag
     * @return Event
     */
    public function on(string $tag): Event
    {
        // Look for existing Event
        $event = $this->events[$tag] ?? null;
        if ($event) { // Found?
            return $event; // Return existing Event
        }

        // Register and return instance of new Event
        return $this->events[$tag] = new Event($this, $tag);
    }

    /**
     * Clear an event
     * @param Event $event
     */
    public function clear(Event $event): void
    {
        unset($this->events[$event->tag()]);
    }

    /**
     * @param string $tag
     * @return Trigger
     */
    public function trigger(string $tag): Trigger
    {
        return new Trigger($this->on($tag));
    }
}