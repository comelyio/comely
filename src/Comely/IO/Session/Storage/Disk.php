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
     * @param \Comely\IO\FileSystem\Disk $disk
     * @throws StorageException
     */
    public function __construct(\Comely\IO\FileSystem\Disk $disk)
    {
        // Check permission
        if (!$disk->privileges()->read) {
            throw new StorageException('Disk instance passed for Session storage does not have READ privilege');
        } elseif (!$disk->privileges()->write) {
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
            throw new StorageException($e->getMessage());
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
        try {
            $file = $this->disk->file($id . ".sess");
            if ($file->is() === \Comely\IO\FileSystem\Disk::IS_FILE) {
                return $file->permissions()->read;
            }
        } catch (DiskException $e) {
        }

        return false;
    }

    /**
     * @param string $id
     * @throws StorageException
     */
    public function delete(string $id): void
    {
        try {
            $this->disk->file($id . ".sess")->delete();
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
                $this->disk->file($file)->delete();
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
            return $this->disk->find("*.sess");
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
            return $this->disk->file($id . ".sess")->lastModified();
        } catch (DiskException $e) {
            throw new StorageException($e->getMessage());
        }
    }
}