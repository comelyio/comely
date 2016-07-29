<?php
declare(strict_types=1);

namespace Comely\IO\Security;

/**
 * Class SecurityException
 * @package Comely\IO\Security
 */
class SecurityException extends \ComelyException
{
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