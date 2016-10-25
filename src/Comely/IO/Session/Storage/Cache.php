<?php
declare(strict_types=1);

namespace Comely\IO\Session\Storage;

use Comely\IO\Session\Exception\StorageException;

/**
 * Class Cache
 * @package Comely\IO\Session\Storage
 */
class Cache implements StorageInterface
{
    /** @var \Comely\IO\Cache\Cache */
    private $cache;

    /**
     * Cache constructor.
     * @param \Comely\IO\Cache\Cache $cache
     */
    public function __construct(\Comely\IO\Cache\Cache $cache)
    {
        $cache->poke(false); // Poke Cache Engine/Server
        $this->cache    =   $cache;
    }

    /**
     * @param string $id
     * @return string
     * @throws StorageException
     */
    public function read(string $id) : string
    {
        $read   =   $this->cache->get("sess_" . $id);
        if(empty($read)) {
            throw StorageException::readError(__METHOD__, 'Not found');
        }

        return $read;
    }

    /**
     * @param string $id
     * @param string $payload
     * @return int
     * @throws StorageException
     */
    public function write(string $id, string $payload) : int
    {
        $bytes  =   strlen($payload);
        $write  =   $this->cache->set("sess_" . $id, $payload);
        if(!$write) {
            $error  =   !empty($this->cache->lastError()) ? $this->cache->lastError() : 'Failed';
            throw StorageException::writeError(__METHOD__, $error);
        }

        return $bytes;
    }

    /**
     * @param string $id
     * @return bool
     * @throws StorageException
     */
    public function delete(string $id) : bool
    {
        $delete =   $this->cache->delete("sess_" . $id);
        if(!$delete) {
            $error  =   !empty($this->cache->lastError()) ? $this->cache->lastError() : 'Failed';
            throw StorageException::deleteError(__METHOD__, $error);
        }

        return true;
    }

    /**
     * @param string $id
     * @return bool
     */
    public function has(string $id) : bool
    {
        return $this->cache->has("sess_" . $id);
    }

    /**
     * @return bool
     * @throws StorageException
     */
    public function flush() : bool
    {
        $flush  =   $this->cache->flush();
        if(!$flush) {
            $error  =   !empty($this->cache->lastError()) ? $this->cache->lastError() : 'Failed';
            throw StorageException::flushError(__METHOD__, $error);
        }

        return true;
    }
}
