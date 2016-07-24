<?php
declare(strict_types=1);

namespace Comely\IO\Toolkit\String;

/**
 * Class Strings
 * @package Comely\IO\Toolkit\String
 */
class Strings
{
    /**
     * Filters a String
     *
     * @param string $str
     * @param string $filters
     * @param bool $spaces
     * @param string $whiteList
     * @return string
     */
    public static function filter(string $str, string $filters = "adsq", bool $spaces = true, string $whiteList = "") : string
    {
        $filters    =   strtolower($filters);
        $filtersCount   =   strlen($filters);
        $pattern    =   "";
        for($i=0;$i<$filtersCount;$i++) {
            switch($filters[$i]) {
                case "a":
                    $pattern    .=  'a-zA-Z';
                    break;
                case "u":
                    $pattern    .=  'A-Z';
                    break;
                case "l":
                    $pattern    .=  'a-z';
                    break;
                case "n":
                    $pattern    .=  '0-9';
                    break;
                case "d":
                    $pattern    .=  '0-9\.';
                    break;
                case "s":
                    $pattern    .=  preg_quote('!@#$%^&*()+=-[];,./_{}|:<>?~\\', '#');
                    break;
                case "q":
                    $pattern    .=  preg_quote('\'""', '#');
                    break;
            }
        }

        if(empty($pattern)) $pattern    =   'a-zA-Z0-9\.';
        if(!empty($whiteList)) $pattern .=  preg_quote($whiteList, '#');
        if($spaces) $pattern    .=  " ";

        return trim(preg_replace(sprintf('#[^%s]*#', $pattern), "", $str));
    }

    /**
     * Evaluates a String as Boolean
     *
     * @param string $str
     * @param \string[] ...$extras
     * @return bool
     */
    public static function evaluate(string $str, string ...$extras) : bool
    {
        $trues  =   array_merge(
            ["true","1","on","yes","enable","enabled"],
            $extras
        );

        return in_array(strtolower($str), $trues);
    }
}