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

namespace Comely\IO\Events;

use Comely\IO\Events\Event\Listeners;
use Comely\IO\Events\Exception\EventsException;

/**
 * Class Event
 * @package Comely\IO\Events
 */
class Event
{
    /** @var EventsHandler */
    private $handler;
    /** @var string */
    private $tag;
    /** @var Listeners */
    private $listeners;

    /**
     * Event constructor.
     * @param EventsHandler $handler
     * @param string $tag
     * @throws EventsException
     */
    public function __construct(EventsHandler $handler, string $tag)
    {
        if (!preg_match('/^[a-zA-Z0-9\.]{2,64}$/', $tag)) {
            throw new EventsException('Invalid event tag');
        }

        $this->handler = $handler;
        $this->listeners = new Listeners($this);
        $this->tag = $tag;
    }

    /**
     * @return string
     */
    public function tag(): string
    {
        return $this->tag;
    }

    /**
     * @return Listeners
     */
    public function listeners(): Listeners
    {
        return $this->listeners;
    }

    /**
     * @return EventsHandler
     */
    public function handler(): EventsHandler
    {
        return $this->handler;
    }

    /**
     * @param callable $callback
     * @return bool
     * @throws Exception\ListenerException
     */
    public function listen(callable $callback): bool
    {
        return $this->listeners->append($callback);
    }
}