<?php
declare(strict_types=1);

namespace Comely\IO\Yaml;

use Comely\IO\Yaml\Exception\ParseException;
use Comely\IO\Yaml\Parser\LinesBuffer;
use Comely\IO\Yaml\Parser\Line;

/**
 * Class Parser
 * Parse YAML files into PHP Array or JSON
 * @package Comely\IO\Yaml
 */
class Parser
{
    private $file;
    private $input;

    const EOL   =   "\n";

    /**
     * Parser constructor.
     * @param string $input path to YAML file
     * @throws ParseException
     */
    public function __construct(string $input = null)
    {
        // Check if $input param was provided with path to YAML file
        if(is_string($input)) {
            $this->readYaml($input);
        }
    }

    /**
     * Reads a YAML file for parsing
     *
     * @param string $input
     * @return Parser
     * @throws ParseException
     */
    public function readYaml(string $input) : self
    {
        // $input param must be provided with path to YAML (.yml|.yaml) file
        if(!preg_match("#^[\w\:\-\_\\\/\.]+\.(yml|yaml)$#", $input)) {
            throw ParseException::badInput();
        }

        // Store YAML file path and content
        $this->file =   $input;
        $this->input    =   @file_get_contents($this->file);
        if(!is_string($this->input)) {
            throw ParseException::fileNotFound($this->file);
        }

        // YAML files are expected in UTF-8 encoding
        if(!preg_match("//u", $this->input)) {
            throw ParseException::badInputUnicode($this->file);
        }

        // Return self
        return $this;
    }

    /**
     * @param string $message
     * @param int $line
     * @throws ParseException
     */
    private function parseError(string $message, int $line)
    {
        throw ParseException::parseError($this->file, $line, $message);
    }


    /**
     * @param LinesBuffer $buffer
     * @return array|string
     * @throws ParseException
     */
    private function parseYaml(LinesBuffer $buffer)
    {
        $bufferLines  =   $buffer->getBufferedData();
        $line   =   new Line($buffer->getLinesOffset());
        $parsed =   [];

        foreach($bufferLines as $bufferLine) {
            $line->read($bufferLine);

            // Check if buffering
            if($buffer->isBuffering()) {
                $subBuffer  =   $buffer->getSubBuffer();

                if($line->isBlank()) {
                    $buffer->addToBuffer($line->value);
                    continue;
                } elseif ($line->indent  >   $subBuffer->getIndent()) {
                    $buffer->addToBuffer($line->value);
                    continue;
                } else {
                    $parsed[$subBuffer->getKey()]   =   $this->parseYaml($subBuffer);
                }

                $buffer->clearSubBuffer();
            }

            // Blank Line
            if($line->isBlank()) {
                continue;
            }

            // Lines must not be indented by tabs
            if($line->value[0]  ===  "\t") {
                $this->parseError("Line must not be indented by tabs", $line->number);
            }

            // Full-line comment
            if(preg_match("/^\s*\#.*$/", $line->value)) {
                continue;
            }

            // Check if line has Key
            if($line->hasKey()) {
                // Clear inline comment
                $line->clearInlineComment();

                // Split line in Key/Value pair
                $split  =   preg_split("/:/", $line->value, 2);
                $key    =   trim($split[0]);
                $value  =   (array_key_exists(1, $split)) ? trim($split[1]) : "";

                if(empty($value)) {
                    // Value not found, start buffering...
                    $buffer->createSubBuffer($key, $line->indent, $line->number);
                    continue;
                } else {
                    // Value found, let's parse
                    try {
                        $value   =   $this->parseValue($value);
                    } catch(ParseException $e) {
                        $this->parseError(sprintf('%s for "%s"', $e->getMessage(), $key), $line->number);
                    }

                    if(is_string($value)    &&  in_array($value, [">","|"])) {
                        // String buffer operators, start buffering...
                        $buffer->createSubBuffer($key, $line->indent, $line->number)->setType($value);
                        continue;
                    } else {
                        $parsed[$key]   =   $value;
                    }
                }
            } else {
                // Key doesn't exist
                // Check current buffer type
                if(in_array($buffer->getType(), [">","|"])) {
                    // String buffer
                    // Clean trailing or leading whitespaces
                    $line->clean();

                    $parsed[]   =   $line->value;
                } else {
                    // Clear inline comment
                    $line->clearInlineComment();

                    // Clean trailing or leading whitespaces
                    $line->clean();

                    if($line->value[0]  === "-") {
                        // Sequence
                        try {
                            $value   =   $this->parseValue(trim(substr($line->value, 1)));
                        } catch(ParseException $e) {
                            $this->parseError($e->getMessage(), $line->number);
                        }

                        // Check for special cases
                        if($buffer->getKey()    === "imports") {
                            // Yaml imports
                            if(is_string($value)) {
                                try {
                                    $value  =   Yaml::Parse($value);
                                } catch(YamlException $e) {
                                    $this->parseError(sprintf("%s imported", $e->getMessage()), $line->number);
                                }
                            } else {
                                $this->parseError('Variable "imports" must be sequence of Yaml files', $line->number);
                            }
                        }

                        $parsed[]   =   $value;
                        continue;
                    } else {
                        // Irrational string
                        // Ignore?
                    }
                }
            }
        }

        // Check for any sub buffer at end of lines
        if($buffer->isBuffering()) {
            $subBuffer  =   $buffer->getSubBuffer();
            $parsed[$subBuffer->getKey()]  =   $this->parseYaml($subBuffer);
        }

        // Final touches, where applicable

        // String buffer...
        if(in_array($buffer->getType(), [">","|"])) {
            $glue   =   ($buffer->getType() === ">") ? " " : self::EOL;
            $parsed =   implode($glue, $parsed);
        }

        // Empty array should be converted to type NULL
        if(count($parsed)   === 0) {
            $parsed =   null;
        }

        // Result cannot be Empty on no-key buffer
        if(empty($parsed)   &&  empty($buffer->getKey())) {
            throw ParseException::badYamlFile($this->file);
        }

        // Imports should be merged with final result
        if(is_array($parsed)) {
            if(array_key_exists("imports", $parsed) &&  is_array($parsed["imports"])) {
                $imported   =   $parsed["imports"];
                unset($parsed["imports"]);
                array_unshift($imported, $parsed);
                $parsed =   call_user_func_array("array_replace_recursive", $imported);
            }
        }

        return $parsed;
    }

    /**
     * @param string $value
     * @return bool|float|int|null|string
     * @throws ParseException
     */
    private function parseValue(string $value)
    {
        $value  =   trim($value);

        // Blank
        if(empty($value)) {
            return NULL;
        }

        // NULL type
        if(in_array(strtolower($value), ["~","null"], true)) {
            return null;
        }

        // Boolean
        if(in_array(strtolower($value), ["true","false","1","0","on","off","yes","no","y","n"])) {
            return (in_array(strtolower($value), ["true","1","on","yes","y"])) ?  true : false;
        }

        // Positive Integer
        if(ctype_digit($value)) {
            return (int) $value;
        }

        // Negative Integer
        if($value[0]    === "-" &&  ctype_digit(substr($value, 1))) {
            return (int) $value;
        }

        // Float
        if(is_numeric($value)   &&  strpos($value, ".") >=  1) {
            return (float) $value;
        }

        // String
        return $this->validateStringValue($value);
    }

    /**
     * @param string $value
     * @return string
     * @throws ParseException
     */
    private function validateStringValue(string $value)
    {
        // Check is String starts with quotes
        if(in_array($value[0], ["'",'"'])) {
            if(substr($value, -1)   === $value[0]   ||  substr($value, -2)  === $value[0] . ",") {
                // Remove matching starting and ending quotes
                $value  =   substr($value, 1, (substr($value, -1) === "," ? -2 : -1));
            } else {
                // String doesn't end with same quote type
                throw new ParseException(__METHOD__, "Unmatched starting and ending quotes");
            }
        }

        // TODO, Inline Sequences and Mapping

        return $value;
    }

    /**
     * @return array
     */
    public function parse() : array
    {
        return $this->parseYaml((new LinesBuffer())->bootstrap(explode(self::EOL, $this->input)));
    }
}