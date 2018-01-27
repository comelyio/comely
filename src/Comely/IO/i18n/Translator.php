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

namespace Comely\IO\i18n;

// Defined global translate functions
require_once __DIR__ . "/Translator/globalTranslateFunctions.php";

use Comely\IO\i18n\Exception\TranslatorException;
use Comely\IO\i18n\Translator\Language;
use Comely\IO\i18n\Translator\TranslatorInterface;
use Comely\IO\Yaml\Yaml;

/**
 * Class Translator
 * @package Comely\IO\i18n
 */
class Translator implements TranslatorInterface
{
    /** @var self */
    private static $instance;

    /** @var string */
    private $languagesPath;
    /** @var null|Language */
    private $boundLanguage;
    /** @var null|Language */
    private $boundFallback;

    /**
     * Disabled Translator constructor.
     */
    private function __construct() {}

    /**
     * @return Translator
     * @throws TranslatorException
     */
    public static function getInstance() : self
    {
        if(!isset(self::$instance)) {
            // Check if global translate functions has already been instanced
            if(!function_exists("__")) {
                throw TranslatorException::initError();
            }

            // Bootstrap translator
            self::$instance =   new self();
            $translator =   self::$instance;
            $translator->languagesPath  =   "./";
        }

        return self::$instance;
    }

    /**
     * @param string $languagesPath
     * @return Translator
     * @throws TranslatorException
     */
    public function setLanguagesPath(string $languagesPath) : self
    {
        // Languages path must have a trailing (/) directory separator
        $languagesPath  =   rtrim($languagesPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        // Check if path is a valid directory
        if(@is_dir($languagesPath)  &&  @is_readable($languagesPath)) {
            // Check if directory has one or more YAML language files
            $languages  =   glob($languagesPath . "*.{yml,yaml}", GLOB_BRACE);
            if(!empty($languages)) {
                $this->languagesPath    =   $languagesPath;
                return $this;
            } else {
                throw TranslatorException::noLanguageFiles($languagesPath);
            }
        } else {
            throw TranslatorException::badLanguagesPath($languagesPath);
        }
    }

    /**
     * @param string $name Language/locale filename without YAML extensions
     * @param bool $returnPath return Boolean (true) or String path to YAML (.yml|.yaml) file on success
     * @return bool|string
     */
    public function languageExists(string $name, bool $returnPath = false)
    {
        $languageFile   =   $this->languagesPath . $name;
        if(@is_readable($languageFile . ".yml")) {
            return (!$returnPath) ? true : $languageFile . ".yml";
        } elseif(@is_readable($languageFile . ".yaml")) {
            return (!$returnPath) ? true : $languageFile . ".yaml";
        } else {
            return false;
        }
    }

    /**
     * @param string $name
     * @return Language
     * @throws TranslatorException
     */
    public function language(string $name) : Language
    {
        // Check if language YAML file exists, return Extension (.yml|.yaml)
        $yamlFile  =   $this->languageExists($name, true);
        if($yamlFile) {
            // Read and parse language file
            $translations   =   Yaml::Parse($yamlFile, Yaml::OUTPUT_ARRAY);
            $language   =   new Language($name);

            // Compile all translations in Comely\IO\i18n\Translator\Language object
            try {
                $this->populateTranslations($language, $translations);
            } catch (TranslatorException $e) {
                // Language files must not have anything else but translations
                throw TranslatorException::languageBadFormat($yamlFile);
            }
            return $language;
        } else {
            throw TranslatorException::languageNotFound($name, $this->languagesPath);
        }
    }

    /**
     * Compile all translations in Comely\IO\i18n\Translator\Language object
     *
     * @param Language $language
     * @param array $translations
     * @param string $prefix
     * @throws TranslatorException
     */
    private function populateTranslations(Language $language, array $translations, string $prefix = "")
    {
        foreach($translations as $key => $translation) {
            // Save into Language object
            $key    =   trim(sprintf("%s.%s", $prefix, $key), ".");

            if(is_string($translation)) {
                $language->set($key, $translation);
            } elseif(is_array($translation)) {
                $this->populateTranslations($language, $translation, $key);
            } else {
                // Language files must not have anything else but translations
                throw new TranslatorException("Unacceptable translated type");
            }
        }
    }

    /**
     * @param Language $lang
     * @return Translator
     */
    public function bindLanguage(Language $lang) : self
    {
        $this->boundLanguage    =   $lang;
        return $this;
    }

    /**
     * @return Language
     * @throws TranslatorException
     */
    public function getBoundLanguage() : Language
    {
        if(!$this->boundLanguage instanceof Language) {
            throw TranslatorException::getBoundError(__METHOD__);
        }

        return $this->boundLanguage;
    }

    /**
     * @param Language $lang
     * @return Translator
     */
    public function bindFallbackLanguage(Language $lang) : self
    {
        $this->boundFallback    =   $lang;
        return $this;
    }

    /**
     * @param Language $lang
     * @return Translator
     */
    public function bindFallback(Language $lang) : self
    {
        return $this->bindFallbackLanguage($lang);
    }

    /**
     * @return Language
     * @throws TranslatorException
     */
    public function getFallbackLanguage() : Language
    {
        if(!$this->boundFallback instanceof Language) {
            throw TranslatorException::getBoundError(__METHOD__);
        }

        return $this->boundFallback;
    }

    /**
     * @param string $key
     * @param Language|null $lang
     * @returns string|null
     * @throws TranslatorException
     */
    public function translate(string $key, Language $lang = null)
    {
        // If a Language object is not passed by reference
        if($lang    === null) {
            // Get default bound language
            $lang   =   $this->boundLanguage;
        }

        // Make sure we've got a Language object
        if(!$lang instanceof Language) {
            throw TranslatorException::translateNoLanguage();
        }

        // Translate!
        $translated =   $lang->get($key);
        if(is_string($translated)) {
            // Translation was found
            return $translated;
        } else {
            // Check if Fallback language object is set
            if($this->boundFallback instanceof Language) {
                return $this->boundFallback->get($key);
            }
        }

        return null;
    }
}