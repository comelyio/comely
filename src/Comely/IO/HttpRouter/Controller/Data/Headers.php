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

namespace Comely\IO\HttpRouter\Controller;

use Comely\IO\HttpRouter\Controller\Data\AbstractIterableData;
use Comely\IO\HttpRouter\Controller\Data\Property;

/**
 * Class Headers
 * @package Comely\IO\HttpRouter\Controller
 */
class Headers extends AbstractIterableData
{
    /**
     * @param string $key
     * @param string $value
     * @return bool
     */
    public function set(string $key, string $value): bool
    {
        return $this->setProp($key, $value);
    }

    /**
     * @param string $key
     * @return Property|null
     */
    public function get(string $key): ?Property
    {
        return $this->getProp($key);
    }
}