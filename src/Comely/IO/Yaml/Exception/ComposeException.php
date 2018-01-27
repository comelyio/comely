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
    public static function badInput() : self
    {
        return new self(self::$componentId, "First argument must be an input associative Array", 1201);
    }

    /**
     * @return ComposeException
     */
    public static function badOutputFile() : self
    {
        return new self(self::$componentId, "Argument passed to \$output must be path to a YAML file", 1202);
    }

    /**
     * @param string $dir
     * @return ComposeException
     */
    public static function outputDirUnwritable(string $dir) : self
    {
        return new self(self::$componentId, sprintf('Directory "%1$s" is not writable', $dir), 1203);
    }

    /**
     * @return ComposeException
     */
    public static function indentRequired() : self
    {
        return new self(self::$componentId, "Indent must be in range of 2 to 10", 1204);
    }

    /**
     * @return ComposeException
     */
    public static function firstTierNonIntegerKey() : self
    {
        return new self(self::$componentId, "All array keys must be String in first tier", 1205);
    }

    /**
     * @return ComposeException
     */
    public static function composeFailed() : self
    {
        return new self(self::$componentId, sprintf('YAML composition failed'), 1206);
    }

    /**
     * @param string $file
     * @return ComposeException
     */
    public static function writeFailed(string $file) : self
    {
        return new self(self::$componentId, sprintf('Failed to write "%1$s" in "%2$s"', basename($file), dirname($file) . DIRECTORY_SEPARATOR), 1207);
    }
}