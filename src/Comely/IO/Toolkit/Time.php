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

namespace Comely\IO\Toolkit;

/**
 * Class Time
 * @package Comely\IO\Toolkit
 */
class Time
{
   /**
    * @param string $units
    * @return int
    */
   public static function unitsToSeconds(string $units) : int
   {
       // Remove all whitespaces
      $units   =  preg_replace("/\s/", "", $units);

      $seconds =  0;
      $matches =  [];
      preg_match_all("/[1-9]+[0-9]*[dhms]/", $units, $matches);
      if(is_array($matches)   && array_key_exists(0, $matches) && is_array($matches[0])) {
         // Grab all matches
         $matches =  $matches[0];
         $units   =  self::unitsTable();

         foreach($matches as $match) {
            $int  =  intval(substr($match, 0, -1));
            $unit =  substr($match, -1);
            if(array_key_exists($unit, $units)) {
               $seconds += $int*$units[$unit];
            }
         }
      }
      
      return $seconds;
   }

   /**
    * Units table
    * @return array
    */
   private static function unitsTable() : array
   {
      return [
         "s"   => 1,
         "m"   => 60,
         "h"   => 3600,
         "d"   => 86400
      ];
   }

    /**
    * Difference in two different timestamps in seconds
    *
    * @param int $stamp1
    * @param int|null $stamp2
    * @return int
    */
    public static function difference(int $stamp1, int $stamp2 = null) : int
    {
        if(empty($stamp2)) {
            $stamp2 =   time();
        }

        return $stamp2 > $stamp1 ?  $stamp2-$stamp1 : $stamp1-$stamp2;
    }

    /**
    * @param int $stamp1
    * @param int|null $stamp2
    * @return float
    */
    public static function minutesDifference(int $stamp1, int $stamp2 = null) : float
    {
        return round(self::difference($stamp1, $stamp2)/60, 1);
    }

    /**
    * @param int $stamp1
    * @param int|null $stamp2
    * @return float
    */
    public static function hoursDifference(int $stamp1, int $stamp2 = null) : float
    {
        return round((self::difference($stamp1, $stamp2)/60)/60, 1);
    }
}