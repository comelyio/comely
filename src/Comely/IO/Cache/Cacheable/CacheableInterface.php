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

namespace Comely\IO\Cache\Cacheable;

use Comely\IO\Cache\Exception\CacheException;

/**
 * Interface CacheableInterface
 * @package Comely\IO\Cache\Cacheable
 */
interface CacheableInterface
{
    /**
     * Get key
     * @return string
     */
    public function key(): string;

    /**
     * Set/get TTL for this value
     * @param int $seconds
     * @return int
     */
    public function ttl(int $seconds = null): int;

    /**
     * Get the age (number of seconds since it was stored in cache)
     * @return int
     */
    public function age(): int;

    /**
     * Get stored data type
     * @return string
     */
    public function type(): string;

    /**
     * Timestamp when this item was stored
     * @return int
     */
    public function storedOn(): int;

    /**
     * @return CacheableInterface
     * @throws CacheException
     */
    public function verifyAge(): self;

    /**
     * @return mixed
     */
    public function yield();
}