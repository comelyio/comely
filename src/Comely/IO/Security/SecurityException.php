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
 * Class SecurityException
 * @package Comely\IO\Security
 */
class SecurityException extends \ComelyException
{
    /** @var string */
    protected static $componentId   =   __NAMESPACE__;

    /**
     * @param string $method
     * @return SecurityException
     */
    public static function incorrectRandomBits(string $method) : self
    {
        return new self($method, "Param. bits must be divisible by 8", 1001);
    }
}