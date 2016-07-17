<?php
declare(strict_types=1);

namespace Comely\IO\Yaml;

/**
 * Class Yaml
 * Parse or compose YAML files for Comely
 * Designed specifically for configuration and translation files
 *
 * @package Comely\IO\Yaml
 */
class Yaml
{
    const OUTPUT_ARRAY  =   0;
    const OUTPUT_JSON   =   1;
    
    private static $parser;
    private static $composer;

    /**
     * Parse YAML files into PHP Array or JSON encoded string
     *
     * @param string $input path to YAML (.yml|.yaml) file
     * @param int $outputFlag
     * @param int $jsonFlag
     * @return array|string
     */
    public static function Parse(string $input, int $outputFlag = 0, int $jsonFlag = 0)
    {
        $parser =   self::getParser();
        $parsed =   $parser->parse($input);
        return ($outputFlag === self::OUTPUT_JSON) ? json_encode($parsed, $jsonFlag) : $parsed;
    }

    /**
     * Get instance of Yaml parser
     * @return Parser
     */
    public static function getParser()
    {
        if(!isset(self::$parser)) {
            self::$parser   =   new Parser();
        }

        return self::$parser;
    }

    /**
     * Compose and save PHP Array as YAML file
     *
     * @param array $input
     * @param string $output path to YAML (.yml|.yaml) file to be written
     * @param int $indent
     * @return bool
     */
    public static function Compose(array $input, string $output, int $indent = 4) : bool
    {
        if(!isset(self::$composer)) {
            self::$composer =   new Composer();
        }

        $composer   =   self::$composer;
        return $composer->setInput($input, $indent)->save($output);
    }

    /**
     * Disabled Yaml constructor.
     */
    private function __construct() {}
}
