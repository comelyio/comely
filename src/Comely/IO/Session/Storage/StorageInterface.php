<?php
declare(strict_types=1);

namespace Comely\IO\Session\Storage;

/**
 * Interface StorageInterface
 * @package Comely\IO\Session\Storage
 */
interface StorageInterface
{
    /**
     * @param string $id
     * @return string
     */
    public function read(string $id) : string;

    /**
     * @param string $id
     * @param string $payload
     * @return int
     */
    public function write(string $id, string $payload) : int;

    /**
     * @param string $id
     * @return bool
     */
    public function delete(string $id) : bool;

    /**
     * @param string $id
     * @return bool
     */
    public function has(string $id) : bool;

    /**
     * @return bool
     */
    public function flush() : bool;
}