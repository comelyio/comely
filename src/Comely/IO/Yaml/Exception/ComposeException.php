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
     * @param string $method
     * @return ComposeException
     */
    public static function badInput(string $method) :ComposeException
    {
        return new self($method, "First argument must be an input associative Array", 1201);
    }

    /**
     * @param string $method
     * @return ComposeException
     */
    public static function badOutputFile(string $method) : ComposeException
    {
        return new self($method, "Argument passed to \$output must be path to a YAML file", 1202);
    }

    /**
     * @param string $method
     * @param string $dir
     * @return ComposeException
     */
    public static function outputDirUnwritable(string $method, string $dir) : ComposeException
    {
        return new self($method, sprintf('Directory "%1$s" is not writable', $dir), 1203);
    }

    /**
     * @param string $method
     * @return ComposeException
     */
    public static function indentRequired(string $method) : ComposeException
    {
        return new self($method, "Indent must be in range of 2 to 10", 1204);
    }

    /**
     * @param string $method
     * @return ComposeException
     */
    public static function firstTierNonIntegerKey(string $method) : ComposeException
    {
        return new self($method, "All array keys must be String in first tier", 1205);
    }

    /**
     * @param string $method
     * @return ComposeException
     */
    public static function composeFailed(string $method) : ComposeException
    {
        return new self($method, sprintf('YAML composition failed'), 1206);
    }

    /**
     * @param string $method
     * @param string $file
     * @return ComposeException
     */
    public static function writeFailed(string $method, string $file) : ComposeException
    {
        return new self($method, sprintf('Failed to write "%1$s" in "%2$s/"', basename($file), dirname($file)), 1207);
    }
}