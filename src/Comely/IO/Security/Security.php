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

namespace Comely\IO\Security;

/**
 * Class Security
 * @package Comely\IO\Security
 */
class Security
{
    /**
     * Returns securely generated key of variable length in hexadecimal representation
     *
     * @param int $bits
     * @return string
     * @throws SecurityException
     */
    public static function randomKey(int $bits = 256) : string
    {
        // $bits must be divisible by 8
        if($bits % 8 != 0) {
            throw SecurityException::incorrectRandomBits(__METHOD__);
        }

        return bin2hex(random_bytes(intval(($bits/8))));
    }
}