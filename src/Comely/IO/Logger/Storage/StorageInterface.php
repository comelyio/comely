<?php
declare(strict_types=1);

namespace Comely\IO\Logger\Storage;

use Comely\IO\Logger\Exception\StorageException;

/**
 * Interface StorageInterface
 * @package Comely\IO\Logger\Storage
 */
interface StorageInterface
{
    /**
     * @param string $in
     * @return string
     */
    public function name(string $in) : string;

    /**
     * @param string $type
     * @param string $name
     * @param string $payload
     * @return string
     */
    public function write(string $type, string $name, string $payload) : string;
}