<?php
declare(strict_types=1);

namespace Comely\IO\Toolkit;

/**
 * Class Numbers
 * @package Comely\IO\Toolkit
 */
class Numbers
{
    /**
     * Checks if an integer is within specified range
     *
     * @param int $num
     * @param int $from
     * @param int $to
     * @return bool
     */
    public static function intRange(int $num, int $from, int $to) : bool
    {
        return ($num    >=  $from   &&  $num    <=  $to) ? true : false;
    }

    /**
     * Checks if a floating point number is within specified range
     *
     * @param float $num
     * @param float $from
     * @param float $to
     * @return bool
     */
    public static function floatRange(float $num, float $from, float $to) : bool
    {
        return ($num    >=  $from   &&  $num    <=  $to) ? true : false;
    }
}