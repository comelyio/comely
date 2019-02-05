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

/**
 * Class Trigger
 * @package Comely\IO\Events\Event
 */
class Trigger
{
    /** @var Event */
    private $event;
    /** @var null|array */
    private $params;
    /** @var bool */
    private $destruct;
    /** @var bool */
    private $quiet;

    /**
     * Trigger constructor.
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
        $this->destruct = false;
        $this->quiet = false;
    }

    /**
     * All arguments passed to this method will be forwarded to callback functions of all listeners
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
     * Clear/remove this event from handler once its fired?
     * @return Trigger
     */
    public function clearOnceFired(): self
    {
        $this->destruct = true;
        return $this;
    }

    /**
     * Enables quiet mode.
     * If a listener's callback function throws an Exception, it will be catched and E_USER_WARNING will be triggered
     * with Exception's class, message and code so that next listener's callback may be fired safely.
     * @return Trigger
     */
    public function quiet(): self
    {
        $this->quiet = true;
        return $this;
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function fire(): int
    {
        $listeners = $this->event->listeners();
        if (!$listeners->count()) { // Have listeners?
            return 0; // No listeners
        }

        $count = 0;
        foreach ($listeners as $i => $callback) {
            try {
                call_user_func_array($callback, $this->params ?? []);
                $count++;
            } catch (\Exception $e) {
                if (!$this->quiet) {
                    throw $e;
                }

                trigger_error(
                    sprintf('[%1$s][#%2$d] %3$s', get_class($e), $e->getCode(), $e->getMessage()),
                    E_USER_WARNING
                );
            }
        }

        // Destruct?
        if ($this->destruct) {
            $this->event->handler()->clear($this->event);
        }

        return $count;
    }
}