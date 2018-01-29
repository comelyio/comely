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

namespace Comely\IO\Cache\Cacheable;

use Comely\IO\Cache\Exception\CacheException;

/**
 * Class CacheItem
 * @package Comely\IO\Cache\Cacheable
 */
class CacheItem implements CacheableInterface
{
    /** @var string */
    private $key;
    /** @var string */
    private $type;
    /** @var string */
    private $data;
    /** @var bool */
    private $dataEncoded;
    /** @var null|string */
    private $instanceOf;
    /** @var int */
    private $ttl;
    /** @var int */
    private $timeStamp;

    /**
     * CacheItem constructor.
     * @param string $key
     * @param $target
     * @param int $ttl
     */
    public function __construct(string $key, $target, int $ttl = 0)
    {
        $this->key = $key;
        $this->type = gettype($target);
        if ($this->type === "object") {
            $this->instanceOf = get_class($target);
        }

        $this->data = $target;
        $this->dataEncoded = false;
        if (in_array($this->type, ["object", "array"])) {
            $this->data = base64_encode(serialize($this->data));
            $this->dataEncoded = true;
        }

        $this->ttl = $ttl;
        $this->timeStamp = time();
    }

    /**
     * Set/get TTL for this value
     * @param int|null $seconds
     * @return int
     */
    public function ttl(int $seconds = null): int
    {
        if (is_int($seconds) && $seconds >= 0) {
            $this->ttl = $seconds;
        }

        return $this->ttl;
    }

    /**
     * Get the age (number of seconds since it was stored in cache)
     * @return int
     */
    public function age(): int
    {
        return time() - $this->timeStamp;
    }

    /**
     * Get key
     * @return string
     */
    public function key(): string
    {
        return $this->key;
    }

    /**
     * Get stored data type
     * @return string
     */
    public function type(): string
    {
        return $this->type;
    }

    /**
     * Timestamp when this item was stored on
     * @return int
     */
    public function storedOn(): int
    {
        return $this->timeStamp;
    }

    /**
     * @return CacheItem
     * @throws CacheException
     */
    public function verifyAge(): CacheableInterface
    {
        if ($this->ttl) {
            if ($this->age() >= $this->ttl) {
                throw new CacheException(sprintf('Cached value of key "%s" has expired', $this->key));
            }
        }

        return $this;
    }

    /**
     * @return mixed
     * @throws CacheException
     */
    public function yield()
    {
        $data = $this->data;
        if ($this->dataEncoded === true) {
            // Unserialize, base64_decode data
            $data = unserialize(base64_decode($data));

            // Cross check stored data type
            if (gettype($data) !== $this->type) {
                throw new CacheException(
                    sprintf(
                        'Key "%s" was stored with data type "%s", returned "%s"',
                        $this->key,
                        $this->type,
                        gettype($data)
                    )
                );
            }
        }

        return $data;
    }
}