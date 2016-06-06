<?php
declare(strict_types=1);

namespace Comely\IO\i18n\Translator;

use Comely\IO\i18n\Translator\Language;

/**
 * Interface TranslatorInterface
 * @package Comely\IO\i18n\Translator
 */
interface TranslatorInterface
{
    /**
     * Set path to directory containing language (YAML) files
     *
     * @param string $languagesPath
     * @return TranslatorInterface
     */
    public function setLanguagesPath(string $languagesPath) : self;

    /**
     * Check if language (YAML) file exists
     *
     * @param string $name Language/locale filename without YAML extensions
     * @param bool $returnPath return Boolean (true) or String path to YAML (.yml|.yaml) file on success
     * @return bool|string
     */
    public function languageExists(string $name, bool $returnPath);

    /**
     * Compile translations from YAML file into a new Comely\IO\i18n\Translator\Language object
     *
     * @param string $name
     * @return \Comely\IO\i18n\Translator\Language
     */
    public function language(string $name) : Language;

    /**
     * Bind a language as default for translations
     *
     * @param \Comely\IO\i18n\Translator\Language $lang
     */
    public function bindLanguage(Language $lang);

    /**
     * Bind a fallback language
     * In case a key cannot be translated in default bound language
     *
     * @param \Comely\IO\i18n\Translator\Language $lang
     */
    public function bindFallback(Language $lang);
    public function bindFallbackLanguage(Language $lang);

    /**
     * Translate a String
     *
     * @param string $key
     * @param \Comely\IO\i18n\Translator\Language|null $lang
     * @return string|null
     */
    public function translate(string $key, Language $lang = null);
}