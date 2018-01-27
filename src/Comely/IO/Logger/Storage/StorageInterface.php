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

namespace Comely\IO\Logger\Storage;


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