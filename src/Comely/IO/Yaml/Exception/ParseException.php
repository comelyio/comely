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
     * @return ParseException
     */
    public static function badInput() : self
    {
        return new self(self::$componentId, "First argument must be path to a YAML file", 1101);
    }

    /**
     * @param string $file
     * @return ParseException
     */
    public static function fileNotFound(string $file) : self
    {
        return new self(
            self::$componentId,
            sprintf(
                'YAML file "%1$s" not found in "%2$s"',
                basename($file),
                dirname($file) . DIRECTORY_SEPARATOR
            ),
            1102
        );
    }

    /**
     * @param string $file
     * @return ParseException
     */
    public static function badInputUnicode(string $file) : self
    {
        return new self(self::$componentId, sprintf('YAML input must be valid UTF-8 in "%1$s"', $file), 1103);
    }

    /**
     * @param string $file
     * @return ParseException
     */
    public static function badYamlFile(string $file) : self
    {
        return new self(self::$componentId, sprintf('An error occurred while parsing "%1$s"', $file), 1104);
    }

    /**
     * @param string $file
     * @param int $line
     * @param string $error
     * @return ParseException
     */
    public static function parseError(string $file, int $line, string $error) : self
    {
        return new self(self::$componentId, sprintf('%1$s in "%2$s" on line %3$d', $error, $file, $line), 1105);
    }
}