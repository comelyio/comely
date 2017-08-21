<?php
/**
 * This file is part of Comely IO package.
 * https://github.com/comelyio/comely
 *
 * Copyright (c) 2016-2017 Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/comelyio/comely/blob/master/LICENSE
 */

declare(strict_types=1);

namespace Comely\IO\i18n\Exception;

use Comely\IO\i18n\i18nException;

/**
 * Class TranslatorException
 * @package Comely\IO\i18n\Exception
 */
class TranslatorException extends i18nException
{
    /** @var string */
    protected static $componentId   =   "Comely\\IO\\i18n\\Translator";

    /**
     * @return TranslatorException
     */
    public static function initError() : self
    {
        return new self(self::$componentId, "Global translator function couldn't be redefined", 1101);
    }

    /**
     * @param string $path
     * @return TranslatorException
     */
    public static function badLanguagesPath(string $path) : self
    {
        return new self(self::$componentId, sprintf('Languages path "%1$s" does not exist', $path), 1102);
    }

    /**
     * @param string $path
     * @return TranslatorException
     */
    public static function noLanguageFiles(string $path) : self
    {
        return new self(self::$componentId, sprintf('No YAML language files were found in "%1$s"', $path), 1103);
    }

    /**
     * @param string $lang
     * @param string $dir
     * @return TranslatorException
     */
    public static function languageNotFound(string $lang, string $dir) : self
    {
        return new self(self::$componentId, sprintf('Language file "%1$s" not found in "%2$s"', $lang, $dir), 1104);
    }

    /**
     * @param string $file
     * @return TranslatorException
     */
    public static function languageBadFormat(string $file) : self
    {
        return new self(
            self::$componentId,
            sprintf(
                'All translations must be of type String in "%1$s"',
                basename($file)
            ),
            1105
        );
    }

    /**
     * @return TranslatorException
     */
    public static function translateNoLanguage() :self
    {
        return new self(self::$componentId, "No language was object passed by reference or bound as default", 1106);
    }

    /**
     * @param string $method
     * @return TranslatorException
     */
    public static function getBoundError(string $method) : self
    {
        return new self($method, 'No language was bound with translator component', 1107);
    }
}