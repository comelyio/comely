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

namespace Comely\IO\Session\Storage;

use Comely\IO\Session\Exception\StorageException;

/**
 * Interface SessionStorageInterface
 * @package Comely\IO\Session\Storage
 */
interface SessionStorageInterface
{
    /**
     * @param string $id
     * @return string
     * @throws StorageException
     */
    public function read(string $id): string;

    /**
     * @param string $id
     * @param $payload
     * @return bool
     * @throws StorageException
     */
    public function write(string $id, $payload): bool;

    /**
     * @param string $id
     * @return void
     * @throws StorageException
     */
    public function delete(string $id): void;

    /**
     * @param string $id
     * @return bool
     * @throws StorageException
     */
    public function has(string $id): bool;

    /**
     * @param string $id
     * @return int
     * @throws StorageException
     */
    public function lastModified(string $id): int;

    /**
     * @return array
     * @throws StorageException
     */
    public function list(): array;

    /**
     * @return void
     * @throws StorageException
     */
    public function flush(): void;
}