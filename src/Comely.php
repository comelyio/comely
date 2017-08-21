<?php
/**
 * This file is part of Comely IO package.
 * https://github.com/comelyio/comely
 *
 * Copyright (c) 2016-2017 Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/comelyio/comely/blob/master/LICENSE
 */

declare(strict_types=1);

/**
 * Class Comely
 * All static helper methods can be found here
 */
class Comely
{
    /** string Comely Version (Major.Minor.Release-Suffix) */
    const VERSION   =   "1.0.1";
    /** int Comely Version (Major * 10000 + Minor * 100 + Release) */
    const VERSION_ID    =   10001;

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
     * @param string $class
     * @return string
     */
    public static function baseClassName(string $class) : string
    {
        return substr(strrchr($class, "\\"), 1);
    }
}