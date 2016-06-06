<?php
declare(strict_types=1);

namespace Comely\IO\Yaml\Exception;

use Comely\IO\Yaml\YamlException;

/**
 * Class ComposeException
 * @package Comely\IO\Yaml\Exception
 */
class ComposeException extends YamlException
{
    /**
     * @return ComposeException
     */
    public static function badInput() : ComposeException
    {
        return new self(self::$componentId, "First argument must be an input associative Array", 1201);
    }

    /**
     * @return ComposeException
     */
    public static function badOutputFile() : ComposeException
    {
        return new self(self::$componentId, "Argument passed to \$output must be path to a YAML file", 1202);
    }

    /**
     * @param string $dir
     * @return ComposeException
     */
    public static function outputDirUnwritable(string $dir) : ComposeException
    {
        return new self(self::$componentId, sprintf('Directory "%1$s" is not writable', $dir), 1203);
    }

    /**
     * @return ComposeException
     */
    public static function indentRequired() : ComposeException
    {
        return new self(self::$componentId, "Indent must be in range of 2 to 10", 1204);
    }

    /**
     * @return ComposeException
     */
    public static function firstTierNonIntegerKey() : ComposeException
    {
        return new self(self::$componentId, "All array keys must be String in first tier", 1205);
    }

    /**
     * @return ComposeException
     */
    public static function composeFailed() : ComposeException
    {
        return new self(self::$componentId, sprintf('YAML composition failed'), 1206);
    }

    /**
     * @param string $file
     * @return ComposeException
     */
    public static function writeFailed(string $file) : ComposeException
    {
        return new self(self::$componentId, sprintf('Failed to write "%1$s" in "%2$s/"', basename($file), dirname($file)), 1207);
    }
}