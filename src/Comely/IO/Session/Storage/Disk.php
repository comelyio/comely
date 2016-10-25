<?php
declare(strict_types=1);

namespace Comely\IO\Session\Storage;

use Comely\IO\Filesystem\FilesystemException;
use Comely\IO\Session\Exception\StorageException;

/**
 * Class Disk
 * @package Comely\IO\Session\Storage
 */
class Disk implements StorageInterface
{
    /** @var \Comely\IO\Filesystem\Disk */
    private $disk;

    /**
     * Disk constructor.
     * @param \Comely\IO\Filesystem\Disk $disk
     * @throws StorageException
     */
    public function __construct(\Comely\IO\Filesystem\Disk $disk)
    {
        if($disk->diskPrivileges()  !== "rw") {
            throw StorageException::diskPrivileges();
        }

        $this->disk =   $disk;
    }

    /**
     * @param string $id
     * @return string
     */
    public function read(string $id) : string
    {
        return $this->disk->read($id . ".sess");
    }

    /**
     * @param string $id
     * @param string $payload
     * @return int
     * @throws StorageException
     */
    public function write(string $id, string $payload) : int
    {
        $write  =   $this->disk->write($id . ".sess", $payload, \Comely\IO\Filesystem\Disk::WRITE_FLOCK);
        if(!$write) {
            throw StorageException::writeError(__METHOD__, 'Failed');
        }

        return $write;
    }

    /**
     * @param string $id
     * @return bool
     */
    public function delete(string $id) : bool
    {
        $this->disk->delete($id . ".sess");
        return true;
    }

    /**
     * @param string $id
     * @return bool
     */
    public function has(string $id) : bool
    {
        return $this->disk->isReadable($id . ".sess");
    }

    /**
     * @return bool
     * @throws StorageException
     */
    public function flush() : bool
    {
        $files  =   $this->disk->find("*.sess");
        $count  =   0;
        foreach($files as $file) {
            try {
                $this->disk->delete($file);
                $count++;
            } catch (FilesystemException $e) {
            }
        }

        if(!$count) {
            throw StorageException::flushError(__METHOD__, sprintf('Flushed %d files', $count));
        }

        return true;
    }
}