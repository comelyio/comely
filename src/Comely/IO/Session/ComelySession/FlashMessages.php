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

namespace Comely\IO\Session\ComelySession;

use Comely\IO\Session\ComelySession\FlashMessages\FlashMessage;

/**
 * Class FlashMessages
 * @package Comely\IO\Session\ComelySession
 */
class FlashMessages implements \Iterator, \Countable
{
    /** @var array */
    private $current;
    /** @var null|array */
    private $loaded;
    /** @var int */
    private $index;
    /** @var int */
    private $count;

    /**
     * FlashMemory constructor.
     */
    public function __construct()
    {
        $this->current = [];
        $this->loaded = [];
        $this->index = 0;
        $this->count = 0;
    }

    /**
     * sleep
     * @return array
     */
    public function __sleep(): array
    {
        $this->loaded = $this->current;
        $this->current = []; // purge current array
        return ["loaded", "current"];
    }

    /**
     * Wakeup
     */
    public function __wakeup(): void
    {
        $this->current = [];
        $this->count = count($this->loaded);
        $this->index = 0;
    }

    /**
     * @param FlashMessage $message
     */
    public function append(FlashMessage $message): void
    {
        $this->current[] = $message;
    }

    /**
     * @param string $message
     * @return FlashMessages
     */
    public function info(string $message): self
    {
        $this->append(new FlashMessage("info", $message));
        return $this;
    }

    /**
     * @param string $message
     * @return FlashMessages
     */
    public function notice(string $message): self
    {
        $this->append(new FlashMessage("notice", $message));
        return $this;
    }

    /**
     * @param string $message
     * @return FlashMessages
     */
    public function success(string $message): self
    {
        $this->append(new FlashMessage("success", $message));
        return $this;
    }

    /**
     * @param string $message
     * @return FlashMessages
     */
    public function warning(string $message): self
    {
        $this->append(new FlashMessage("warning", $message));
        return $this;
    }

    /**
     * @param string $message
     * @return FlashMessages
     */
    public function error(string $message): self
    {
        $this->append(new FlashMessage("error", $message));
        return $this;
    }

    /**
     * @param string $message
     * @return FlashMessages
     */
    public function danger(string $message): self
    {
        $this->append(new FlashMessage("danger", $message));
        return $this;
    }

    /**
     * @return array
     */
    public function array(): array
    {
        $messages = [];
        /** @var FlashMessage $message */
        foreach ($this->loaded as $message) {
            $messages[] = [
                "type" => $message->type(),
                "message" => $message->message(),
            ];
        }

        return $messages;
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
     * @return int
     */
    public function key(): int
    {
        return $this->index;
    }

    /**
     * @return FlashMessage
     */
    public function current(): FlashMessage
    {
        return $this->loaded[$this->index];
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
        return isset($this->loaded[$this->index]);
    }
}