<?php
declare(strict_types=1);

/**
 * Class Comely
 * All static helper methods can be found here
 */
class Comely {

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
}