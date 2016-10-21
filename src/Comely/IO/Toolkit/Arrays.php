<?php
declare(strict_types=1);

namespace Comely\IO\Toolkit;

/**
 * Class Arrays
 * @package Comely\IO\Toolkit
 */
class Arrays
{
    /**
     * Checks if input $array has all of $keys keys
     *
     * @param array $array
     * @param array $keys
     * @return bool
     */
    public static function hasKeys(array $array, array $keys) : bool
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
     * Returns new array containing $keys from $array, or an empty array if no keys were matched
     *
     * @param array $array
     * @param array $keys
     * @return array
     */
    public static function clone(array $array, array $keys) : array
    {
        $clone  =   [];
        foreach($keys as $key) {
            if(array_key_exists($key, $array)) {
                $clone[$key]    =   $array[$key];
            }
        }
        
        return $clone;
    }

    /**
     * Searches an indexed array containing associative arrays (i.e. multiple rows from database query)
     *
     * Returns matching array if $limit is 1
     * Returns indexed array containing all arrays with matching key/values, if $limit is > 1
     *
     * @param array $array
     * @param string $key
     * @param $value
     * @param int $limit
     * @return array|bool
     */
    public static function search(array $array, string $key, $value, int $limit = 0)
    {
        $matched    =   [];
        $matches    =   0;

        // Lowercase string for case insensitive matching
        if(is_string($value)) {
            $value  =   strtolower($value);
        }

        // Iterate through indexed array
        foreach($array as $entry) {
            // Check if index is an Array and has the key that we are searching with
            if(is_array($entry) &&  array_key_exists($key, $entry)) {
                // Lowercase string for case insensitive matching
                $search =   (is_string($entry[$key])) ? strtolower($entry[$key]) : $entry[$key];
                // Compare value with searched value
                if($search  === $value) {
                    // Positive match
                    $matches++;
                    $matched[]  =   $entry;

                    if($limit   >   0   &&  $limit  >=  $matches) {
                        break;
                    }
                }
            }
        }

        // Return
        if($limit   === 1) {
            return array_key_exists(0, $matched) ? $matched[0] : false;
        } else {
            return $matches >   0 ? $matched : false;
        }
    }
}