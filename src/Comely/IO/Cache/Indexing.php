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

namespace Comely\IO\Cache;

use Comely\IO\Cache\Exception\CacheException;
use Comely\IO\Cache\Indexing\Keys;
use Comely\IO\Events\EventsHandler;

/**
 * Class Indexing
 * @package Comely\IO\Cache
 */
class Indexing
{
    private const INDEXING_KEY = "~comely-CacheIndexedKeys";

    public const EVENT_ON_STORE = "onStore";
    public const EVENT_ON_DELETE = "onDelete";
    public const EVENT_ON_FLUSH = "onFlush";

    /** @var Cache */
    private $cache;
    /** @var EventsHandler */
    private $events;
    /** @var Keys */
    private $keys;

    /**
     * Indexing constructor.
     * @param Cache $cache
     * @throws \Comely\IO\Events\Exception\ListenerException
     */
    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
        $this->events = new EventsHandler();
        $this->load(); // Load currently saved keys

        // Register Shutdown Function
        register_shutdown_function([$this, "save"]);

        // Register Events
        $this->registerEvents();
    }

    /**
     * Load stored keys from Cache or create new index
     */
    public function load(): void
    {
        try {
            $keys = $this->cache->get(self::INDEXING_KEY);
            if ($keys instanceof Keys) {
                $this->keys = $keys;
                return;
            }
        } catch (CacheException $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);
        }

        $this->keys = new Keys();
    }

    /**
     * @throws CacheException
     */
    public function save(): void
    {
        try {
            $this->cache->set(self::INDEXING_KEY, clone $this->keys);
        } catch (CacheException $e) {
            // Assuming this method will run at end of execution, trigger an error based on caught exception
            // so that a logger (if in place) can log this, before we rethrow this exception
            trigger_error($e->getMessage(), E_USER_WARNING);

            throw $e;
        }
    }

    /**
     * @return EventsHandler
     */
    public function events(): EventsHandler
    {
        return $this->events;
    }

    /**
     * @throws \Comely\IO\Events\Exception\ListenerException
     */
    private function registerEvents(): void
    {
        $onStore = $this->events->on(self::EVENT_ON_STORE);
        $onStore->listen(function (string $key, string $type) {
            $this->keys->append($key, $type);
        });

        $onDelete = $this->events->on(self::EVENT_ON_DELETE);
        $onDelete->listen(function (string $key) {
            $this->keys->delete($key);
        });

        $onFlush = $this->events->on(self::EVENT_ON_FLUSH);
        $onFlush->listen(function () {
            $this->keys->flush();
        });
    }
}