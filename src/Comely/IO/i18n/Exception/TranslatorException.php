<?php
declare(strict_types=1);

namespace Comely\IO\i18n\Exception;

use Comely\IO\i18n\i18nException;

/**
 * Class TranslatorException
 * @package Comely\IO\i18n\Exception
 */
class TranslatorException extends i18nException
{
    protected static $componentId   =   "Comely\\IO\\i18n\\Translator";

    /**
     * @return TranslatorException
     */
    public static function initError() : TranslatorException
    {
        return new self(self::$componentId, "Global translator function couldn't be defined", 1101);
    }

    /**
     * @param string $path
     * @return TranslatorException
     */
    public static function badLanguagesPath(string $path) : TranslatorException
    {
        return new self(self::$componentId, sprintf('Languages path "%1$s" does not exist', $path), 1102);
    }

    /**
     * @param string $path
     * @return TranslatorException
     */
    public static function noLanguageFiles(string $path) : TranslatorException
    {
        return new self(self::$componentId, sprintf('No YAML language files were found in "%1$s"', $path), 1103);
    }

    /**
     * @param string $lang
     * @param string $dir
     * @return TranslatorException
     */
    public static function languageNotFound(string $lang, string $dir) : TranslatorException
    {
        return new self(self::$componentId, sprintf('Language file "%1$s" not found in "%2$s"', $lang, $dir), 1104);
    }

    /**
     * @param string $file
     * @return TranslatorException
     */
    public static function languageBadFormat(string $file) : TranslatorException
    {
        return new self(self::$componentId, sprintf('All translations must be of type String in "%1$s"', basename($file)), 1105);
    }

    /**
     * @return TranslatorException
     */
    public static function translateNoLanguage() :TranslatorException
    {
        return new self(self::$componentId, "No language was object passed by reference or bound as default", 1106);
    }
}