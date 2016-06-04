<?php
declare(strict_types=1);

namespace Comely\IO\Yaml\Exception;

use Comely\IO\Yaml\YamlException;

/**
 * Class ParseException
 * @package Comely\IO\Yaml\Exception
 */
class ParseException extends YamlException
{
    /**
     * @param string $method
     * @return ParseException
     */
    public static function badInput(string $method) : ParseException
    {
        return new self($method, "First argument must be path to a YAML file", 1101);
    }

    /**
     * @param string $method
     * @param string $file
     * @return ParseException
     */
    public static function fileNotFound(string $method, string $file) : ParseException
    {
        return new self($method, sprintf('YAML file "%1$s" not found in "%2$s/"', basename($file), dirname($file)), 1102);
    }

    /**
     * @param string $method
     * @param string $file
     * @return ParseException
     */
    public static function badInputUnicode(string $method, string $file) : ParseException
    {
        return new self($method, sprintf('YAML input must be valid UTF-8 in "%1$s"', $file), 1103);
    }

    /**
     * @param string $method
     * @param string $file
     * @return ParseException
     */
    public static function badYamlFile(string $method, string $file)
    {
        return new self($method, sprintf('An error occured while parsing "%1$s"', $file), 1104);
    }

    /**
     * @param string $method
     * @param string $file
     * @param int $line
     * @param string $error
     * @return ParseException
     */
    public static function parseError(string $method, string $file, int $line, string $error)
    {
        return new self($method, sprintf('%1$s in "%2$s" on line %3$d', $error, $file, $line), 1105);
    }
}