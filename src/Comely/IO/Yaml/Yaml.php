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
    const INDENT    =   4;

    /**
     * @param string $input valid YAML body or path to a YAML file
     * @param int $outputFlag
     * @param int $jsonFlag
     * @return array|string
     */
    public static function Parse(string $input, int $outputFlag = 0, int $jsonFlag = 0)
    {
        $parsed =   (new Parser($input))->parse();
        return ($outputFlag === self::OUTPUT_JSON) ? json_encode($parsed, $jsonFlag) : $parsed;
    }
}
