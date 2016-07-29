<?php
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