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

namespace Comely\IO\Toolkit;

use Comely\IO\Toolkit\Exception\ParserException;
use Comely\IO\Toolkit\Parser\Modifiers;

/**
 * Class Parser
 * @package Comely\IO\Toolkit
 */
class Parser
{
    /** @var self */
    private static $instance;
    /** @var Modifiers */
    private $modifiers;

    /**
     * Disabled Parser constructor.
     */
    private function __construct() {}

    /**
     * @return Parser
     */
    public static function getInstance() : self
    {
        if(!isset(self::$instance)) {
            self::$instance =   new self();
            self::$instance->modifiers  =   new Modifiers();
        }

        return self::$instance;
    }

    /**
     * @param string $str
     * @param $data
     * @return string
     * @throws ParserException
     */
    public function parse(string $str, $data) : string
    {
        if(is_object($data)) {
            // Convert object to array
            $data   =   json_decode(json_encode($data), true);
        }
        
        if(!is_array($data)) {
            // Expects $data param to be supplied with an Array
            throw ParserException::badData();
        }

        // Map data
        $mapped =   $this->dataMapper($data);

        // Start resolving variables
        $parsed =   preg_replace_callback(
            "/%[a-zA-Z0-9_|.]+%/",
            function($variable) use($mapped) {
                // Explode variable in key and modifiers
                $variable   =   explode("|", substr($variable[0], 1, -1));
                $replace    =   "";

                // Check if mapped replacement value exists
                if(array_key_exists($variable[0], $mapped)) {
                    // Replacement value found
                    $replace    =   $mapped[$variable[0]];

                    // Get modifiers
                    $modifiers  =   $variable;
                    unset($modifiers[0]);

                    // Apply modifiers
                    foreach($modifiers as $modifier) {
                        if(method_exists($this->modifiers, $modifier)) {
                            // Modifier found, Apply
                            $replace    =   call_user_func([$this->modifiers,$modifier], $replace);
                        }
                    }
                }

                return $replace;
            },
            $str
        );

        return $parsed;
    }

    /**
     * @param array $data
     * @param string $prefix
     * @return array
     */
    private function dataMapper(array $data, string $prefix = "") : array
    {
        $mapped =   [];
        foreach($data as $key => $value)  {
            $mapKey =   !empty($prefix) ? sprintf("%s.%s", $prefix, $key) : $key;
            if(is_array($value)) {
                $mapped =   array_merge($mapped, $this->dataMapper($value, $mapKey));
            } elseif(is_scalar($value)) {
                $mapped[$mapKey]    =   $value;
            } elseif(is_null($value)) {
                $mapped[$mapKey]    =   "~";
            }
        }

        return $mapped;
    }
}