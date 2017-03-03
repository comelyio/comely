<?php
declare(strict_types=1);

namespace Comely\IO\Logger\Storage;

use Comely\IO\Logger\Exception\StorageException;

/**
 * Class Disk
 * @package Comely\IO\Logger\Storage
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
        if(!$disk->diskPrivileges() !== "rw") {
            throw StorageException::diskPrivileges();
        }

        $this->disk =   $disk;
    }

    /**
     * @param string $in
     * @return string
     */
    public function name(string $in) : string
    {
        return sprintf('%s.log', strtolower($in));
    }

    /**
     * @param string $type
     * @param string $name
     * @param string $payload
     * @return string
     * @throws StorageException
     */
    public function write(string $type, string $name, string $payload) : string
    {
        $fileName   =   sprintf('%s%s%s', $type, DIRECTORY_SEPARATOR, $this->name($name));
        $write  =   $this->disk->write($fileName, $payload, \Comely\IO\Filesystem\Disk::WRITE_FLOCK);
        if(!$write) {
            throw StorageException::writeError(__METHOD__, 'Failed');
        }

        return $fileName;
    }
}