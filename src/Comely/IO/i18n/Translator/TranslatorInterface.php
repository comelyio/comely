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

namespace Comely\IO\i18n\Translator;

use Comely\IO\i18n\Translator;

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
     * @return Translator
     */
    public function setLanguagesPath(string $languagesPath) : Translator;

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
    public function language(string $name): Language;
    
    /**
     * Bind a language as default for translations
     *
     * @param \Comely\IO\i18n\Translator\Language $lang
     * @return Translator
     */
    public function bindLanguage(Language $lang) : Translator;

    /**
     * Bind a fallback language
     * In case a key cannot be translated in default bound language
     *
     * @param \Comely\IO\i18n\Translator\Language $lang
     * @return Translator
     */
    public function bindFallback(Language $lang) : Translator;
    public function bindFallbackLanguage(Language $lang) : Translator;

    /**
     * Translate a String
     *
     * @param string $key
     * @param \Comely\IO\i18n\Translator\Language|null $lang
     * @return string|null
     */
    public function translate(string $key, Language $lang = null);
}