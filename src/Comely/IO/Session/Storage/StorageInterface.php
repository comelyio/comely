<?php
/**
 * This file is part of Comely IO package.
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