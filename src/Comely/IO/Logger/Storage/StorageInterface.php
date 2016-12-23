<?php
declare(strict_types=1);

namespace Comely\IO\Logger\Storage;

/**
 * Interface StorageInterface
 * @package Comely\IO\Logger\Storage
 */
interface StorageInterface
{

    public function list() : array;
}