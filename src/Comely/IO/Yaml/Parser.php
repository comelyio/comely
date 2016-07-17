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
    private $baseDir;

    const EOL   =   "\n";

    /**
     * @param string $dir
     */
    public function setBaseDir(string $dir)
    {
        $this->baseDir  =   rtrim($dir, DIRECTORY_SEPARATOR);
    }

    /**
     * @param string $file
     * @return string
     */
    private function importPath(string $file)
    {
        return $this->baseDir . DIRECTORY_SEPARATOR . $file;
    }

    /**
     * @param string $message
     * @param string $file
     * @param int $line
     * @throws ParseException
     */
    private function parseError(string $message, string $file, int $line)
    {
        throw ParseException::parseError($file, $line, $message);
    }

    /**
     * @param LinesBuffer $buffer
     * @param string $filePath
     * @return array|string
     * @throws ParseException
     */
    private function parseYaml(LinesBuffer $buffer, string $filePath)
    {
        $dirPath    =   dirname($filePath);
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
                    $parsed[$subBuffer->getKey()]   =   $this->parseYaml($subBuffer, $filePath);
                }

                $buffer->clearSubBuffer();
            }

            // Blank Line
            if($line->isBlank()) {
                continue;
            }

            // Lines must not be indented by tabs
            if($line->value[0]  ===  "\t") {
                $this->parseError("Line must not be indented by tabs", $filePath, $line->number);
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
                        $this->parseError(sprintf('%s for "%s"', $e->getMessage(), $key), $filePath, $line->number);
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
                            $this->parseError($e->getMessage(), $filePath, $line->number);
                        }

                        // Check for special cases
                        if($buffer->getKey()    === "imports") {
                            // Yaml imports
                            if(is_string($value)) {
                                try {
                                    $value  =   Yaml::Parse($this->importPath($value));
                                } catch(YamlException $e) {
                                    $this->parseError(
                                        sprintf(
                                            "%s imported",
                                            $e->getMessage()
                                        ),
                                        $filePath,
                                        $line->number
                                    );
                                }
                            } else {
                                $this->parseError(
                                    'Variable "imports" must be sequence of Yaml files',
                                    $filePath,
                                    $line->number
                                );
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
            $parsed[$subBuffer->getKey()]  =   $this->parseYaml($subBuffer, $filePath);
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
            throw ParseException::badYamlFile($filePath);
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
     * @param string $input
     * @return array
     * @throws ParseException
     */
    public function parse(string $input) : array
    {
        // $input param must be provided with path to YAML (.yml|.yaml) file
        if(!preg_match("#^[\w\:\-\_\\\/\.]+\.(yml|yaml)$#", $input)) {
            throw ParseException::badInput();
        }

        // Store YAML file path and content
        $filePath   =   $input;
        $input    =   @file_get_contents($filePath);
        if(!is_string($input)) {
            throw ParseException::fileNotFound($filePath);
        }

        // YAML files are expected in UTF-8 encoding
        if(!preg_match("//u", $input)) {
            throw ParseException::badInputUnicode($filePath);
        }

        // Return self
        return $this->parseYaml(
            (new LinesBuffer())->bootstrap(explode(self::EOL, $input)),
            $filePath
        );
    }
}