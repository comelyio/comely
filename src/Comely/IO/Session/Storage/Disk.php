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

namespace Comely\IO\Session\Storage;

use Comely\IO\FileSystem\Disk\Directory;
use Comely\IO\FileSystem\Exception\DiskException;
use Comely\IO\Session\Exception\StorageException;

/**
 * Class Disk
 * @package Comely\IO\Session\Storage
 */
class Disk implements SessionStorageInterface
{
    /** @var \Comely\IO\FileSystem\Disk */
    private $disk;

    /**
     * Disk constructor.
     * @param Directory $disk
     * @throws StorageException
     */
    public function __construct(Directory $disk)
    {
        // Check permission
        if (!$disk->permissions()->read) {
            throw new StorageException('Disk instance passed for Session storage does not have READ privilege');
        } elseif (!$disk->permissions()->write) {
            throw new StorageException('Disk instance passed for Session storage does not have WRITE privilege');
        }

        $this->disk = $disk;
    }

    /**
     * @param string $id
     * @return string
     * @throws StorageException
     */
    public function read(string $id): string
    {
        try {
            return $this->disk->file($id . ".sess")->read();
        } catch (DiskException $e) {
            throw new StorageException(sprintf('Session "%s" not found in sessions directory', $id));
        }
    }

    /**
     * @param string $id
     * @param $payload
     * @return bool
     * @throws StorageException
     */
    public function write(string $id, $payload): bool
    {
        try {
            $write = $this->disk->write($id . ".sess", $payload, true);
            return $write ? true : false;
        } catch (DiskException $e) {
            throw new StorageException($e->getMessage());
        }
    }

    /**
     * @param string $id
     * @return bool
     */
    public function has(string $id): bool
    {
        return is_readable($this->disk->suffixed($id . ".sess"));
    }

    /**
     * @param string $id
     * @throws StorageException
     */
    public function delete(string $id): void
    {
        try {
            $this->disk->delete($id . ".sess");
        } catch (DiskException $e) {
            throw new StorageException($e->getMessage());
        }
    }

    /**
     * @throws \Comely\IO\FileSystem\Exception\PathException
     */
    public function flush(): void
    {
        $files = $this->list();
        foreach ($files as $file) {
            try {
                $this->disk->delete($file);
            } catch (DiskException $e) {
                trigger_error($e->getMessage(), E_USER_WARNING);
            }
        }
    }

    /**
     * @return array
     * @throws \Comely\IO\FileSystem\Exception\PathException
     */
    public function list(): array
    {
        try {
            return $this->disk->glob("*.sess");
        } catch (DiskException $e) {
            throw new StorageException($e->getMessage());
        }
    }

    /**
     * @param string $id
     * @return int
     * @throws StorageException
     */
    public function lastModified(string $id): int
    {
        try {
            return $this->disk->lastModified($id . ".sess");
        } catch (DiskException $e) {
            throw new StorageException($e->getMessage());
        }
    }
}