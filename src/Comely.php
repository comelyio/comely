<?php
declare(strict_types=1);

/**
 * Class Comely
 * All static helper methods can be found here
 */
class Comely
{
    /**
     * Converts given string (i.e. snake_case) to PascalCase
     *
     * @param string $name
     * @return string
     */
    public static function pascalCase(string $name) : string
    {
        $words  =   preg_split("/[^a-zA-Z0-9]+/", strtolower($name), 0, PREG_SPLIT_NO_EMPTY);
        return implode("", array_map(function($word) {
            return ucfirst($word);
        }, $words));
    }

    /**
     * Converts given string (i.e. snake_case) to camelCase
     *
     * @param string $name
     * @return string
     */
    public static function camelCase(string $name) : string
    {
        // Return an empty String if input is an empty String
        if(empty($name)) {
            return "";
        }

        // Convert to PascalCase first and then convert PascalCase to camelCase
        $pascal =   self::pascalCase($name);
        return sprintf("%s%s", strtolower($pascal[0]), substr($pascal, 1));
    }

    /**
     * Converts given string (i.e. PascalCase or camelCase) to snake_case
     *
     * @param string $name
     * @return string
     */
    public static function snakeCase(string $name) : string
    {
        // Return an empty String if input is an empty String
        if(empty($name)) {
            return "";
        }

        // Convert PascalCase word to camelCase
        $name    =   sprintf("%s%s", strtolower($name[0]), substr($name, 1));

        // Split words
        $words  =   preg_split("/([A-Z0-9]+)/", $name, 0, PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);
        $wordsCount =   count($words);
        $snake  =   $words[0];

        // Iterate through words
        for($i=1;$i<$wordsCount;$i++) {
            if($i % 2   !=  0) {
                // Add an underscore on an odd $i
                $snake  .=  "_";
            }

            // Add word to snake
            $snake  .=  $words[$i];
        }

        // Return lowercase snake
        return strtolower($snake);
    }

    /**
     * Check if $array has all $keys
     *
     * @param array $array
     * @param array $keys
     * @return bool
     */
    public static function arrayHasKeys(array $array, array $keys) : bool
    {
        // Iterate through keys
        foreach($keys as $key) {
            // Check if key exists in input Array
            if(!array_key_exists($key, $array)) {
                // Return FALSE is key doesn't exist in input Array
                return false;
            }
        }

        // Array is comprised of all keys, return TRUE
        return true;
    }

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
     * @param string $class
     * @return string
     */
    public static function baseClassName(string $class) : string
    {
        return substr(strrchr($class, "\\"), 1);
    }
}