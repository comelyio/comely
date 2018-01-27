<?php
declare(strict_types=1);

namespace Comely\Engine;

/**
 * Class Comely
 * @package Comely\Engine
 */
class Comely
{
    /** string Comely Version (Major.Minor.Release-Suffix) */
    const VERSION = "2.0.0";
    /** int Comely Version (Major * 10000 + Minor * 100 + Release) */
    const VERSION_ID = 20000;

    /**
     * @param string $class
     * @return string
     */
    public static function baseClassName(string $class) : string
    {
        return substr(strrchr($class, "\\"), 1);
    }
}