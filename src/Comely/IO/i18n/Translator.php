<?php
declare(strict_types=1);

namespace Comely\IO\i18n;

// Defined global translate functions
require __DIR__ . DIRECTORY_SEPARATOR . "Translator" . DIRECTORY_SEPARATOR . "globalTranslateFunctions.php";

use Comely\IO\i18n\Exception\TranslatorException;
use Comely\IO\i18n\Translator\Language;
use Comely\IO\i18n\Translator\TranslatorInterface;
use Comely\IO\Yaml\Yaml;
use Comely\Kernel\Repository;

/**
 * Class Translator
 * @package Comely\IO\i18n
 */
class Translator extends Repository implements TranslatorInterface
{
    private $languagesPath;
    private $boundLanguage;
    private $boundFallback;

    /**
     * Translator constructor.
     */
    public function __construct()
    {
        // Check if Translator has already been instantiated
        if(!function_exists("__")) {
            throw TranslatorException::initError();
        }

        // Set languages path to current directory
        $this->languagesPath    =   "./";
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
            foreach($translations as $key => $translation) {
                // Language files must not have anything else but translations
                if(is_string($translation)) {
                    // Save into Language object
                    $language->set($key, $translation);
                } else {
                    throw TranslatorException::languageBadFormat($yamlFile);
                }
            }

            return $language;
        } else {
            throw TranslatorException::languageNotFound($name, $this->languagesPath);
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