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

namespace Comely\IO\Yaml;

use Comely\IO\Yaml\Exception\ComposeException;

/**
 * Class Composer
 * Compose and save PHP Array as YAML file
 * @package Comely\IO\Yaml
 */
class Composer
{
    const EOL  =   "\n";

    /** @var array|null */
    private $input;
    /** @var int */
    private $indent;

    /**
     * Composer constructor.
     * @param array $input
     * @param int $indent
     * @throws ComposeException
     */
    public function __construct(array $input = null, int $indent = 4)
    {
        // Check if $input param was provided with an Array to compose as YAML
        if(is_array($input)) {
            $this->setInput($input, $indent);
        }
    }

    /**
     * @param array $input
     * @param int $indent
     * @return Composer
     * @throws ComposeException
     */
    public function setInput(array $input, int $indent = 4) : self
    {
        // Check if input is non-empty associative Array
        if(empty($input)    ||  is_int(key($input))) {
            throw ComposeException::badInput();
        }

        // Check if valid indent is provided
        if($indent  <  2   ||   $indent >   10) {
            throw ComposeException::indentRequired();
        }

        $this->input    =   $input;
        $this->indent   =   $indent;

        // Return self
        return $this;
    }

    /**
     * @param string $output
     * @return bool
     * @throws ComposeException
     */
    public function save(string $output) : bool
    {
        // Check if $output path leads to valid looking YAML file
        if(!preg_match("#^[\w\:\-\_\\\/\.]+\.(yml|yaml)$#", $output)) {
            throw ComposeException::badOutputFile();
        }

        // Check if directory is writable
        if(!@is_writable(dirname($output))) {
            throw ComposeException::outputDirUnwritable(dirname($output));
        }

        // Generate Yaml
        $yaml   =   $this->getYaml($this->input);
        $lb =   self::EOL;

        // Headers
        $headers    =   "# " . __CLASS__ . $lb;
        $headers    .=  "# This YAML file was composed using Comely YAML Component" . $lb;
        $headers    .=  "# https://github.com/comelyio/comely" . $lb;

        // Write YAML file
        $content    =   $headers . $lb;
        $content    .=  $yaml;

        $write  =   @file_put_contents($output, $content, LOCK_EX);
        if(!$write) {
            throw ComposeException::writeFailed($output);
        }

        return true;
    }

    /**
     * @return string
     */
    public function compose() : string
    {
        return $this->getYaml($this->input);
    }

    /**
     * @param array $input
     * @param int $tier
     * @return string
     * @throws ComposeException
     */
    private function getYaml(array $input, int $tier = 0) : string
    {
        $composed   =   "";
        $indent =   $this->indent*$tier;
        $lb =   self::EOL;

        // Last value type
        // 1: Scalar, 0: Non-scalar
        $lastKey    =   null;
        $lastValueType  =   1;

        // Iterate through input
        foreach($input as $key => $value) {
            // In first tier all keys must be String
            if($tier    === 1   &&  !is_string($key)) {
                throw ComposeException::firstTierNonIntegerKey();
            }

            if(is_scalar($value)    ||  is_null($value)) {
                // Scalar or Null
                // Create blank line if last value was Non-scalar
                if($lastValueType   !== 1) {
                    $composed   .=  $lb;
                }

                // Save this value as scalar for next iteration
                $lastValueType  =   1;


                // Set mapping key or sequence dash
                if(is_string($key)) {
                    $composed   .=  sprintf("%s%s: ", $this->getIndentation($indent), $key);
                } else {
                    $composed   .=  sprintf("%s- ", $this->getIndentation($indent));
                }

                // Set value
                if(is_bool($value)) {
                    $composed   .=  ($value === true) ? "true" : "false";
                } elseif(is_null($value)) {
                    $composed   .=  "~";
                } elseif(is_int($value)) {
                    $composed   .=  $value;
                } elseif(is_float($value)) {
                    $composed   .=  $value;
                } else {
                    // Definitely a String

                    if(strpos($value, $lb) !==    false) {
                        // Multi-line String
                        $composed   .=  "|" . $lb;
                        $lines =   explode($lb, $value);
                        $subIndent    =   $this->getIndentation(($indent+$this->indent));

                        foreach($lines as $line) {
                            $composed   .=  sprintf("%s%s%s", $subIndent, $line, $lb);
                        }
                    } else {
                        if(strlen($value)   >   120) {
                            // Long String, should be wrapped
                            $composed   .=  ">" . $lb;
                            $lines  =   explode($lb, wordwrap($value, 120, $lb));
                            $subIndent    =   $this->getIndentation(($indent+$this->indent));

                            foreach($lines as $line) {
                                $composed   .=  sprintf("%s%s%s", $subIndent, $line, $lb);
                            }
                        } else {
                            // Just another String
                            $composed   .=  sprintf('"%s"', addslashes($value));
                        }
                    }
                }

                $composed   .=  $lb;
            } else {
                // Non-scalar
                // Create blank line if last value was scalar
                if($lastValueType   === 1) {
                    $composed   .=  $lb;
                }

                // Save this value as non-scalar for next iteration
                $lastValueType  =   0;

                // Check if value is Object
                if(is_object($value)) {
                    // Convert to Array
                    // What's smartest way to convert multi-dimensional object?
                    $value  =   json_decode(json_encode($value), true);
                }

                // Ensure non-scalar value is Array
                if(is_array($value)) {
                    $composed   .=  sprintf("%s%s:%s", $this->getIndentation($indent), $key, $lb);
                    $composed   .=  $this->getYaml($value, ($tier+1));
                }
            }
        }

        if(empty($composed) ||  ctype_space($composed)) {
            throw ComposeException::composeFailed();
        }

        $composed   .=  $lb;

        return $composed;
    }

    /**
     * @param int $count
     * @return string
     */
    private function getIndentation(int $count = 0) : string
    {
        return str_repeat(" ", $count);
    }
}