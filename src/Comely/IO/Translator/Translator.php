<?php
/**
 * This file is part of Comely package.
 * https://github.com/comelyio/comely
 *
 * Copyright (c) 2016-2018 Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/comelyio/comely/blob/master/LICENSE
 */

declare(strict_types=1);

namespace Comely\IO\Translator;

/**
 * Load GLOBAL translation functions
 */
require_once __DIR__ . DIRECTORY_SEPARATOR . "globalTranslationFunctions.php";

use Comely\IO\FileSystem\Disk\Directory;
use Comely\IO\Translator\Cache\DiskCache;
use Comely\IO\Translator\Cache\LanguageCacheInterface;
use Comely\IO\Translator\Exception\LanguageException;
use Comely\IO\Translator\Exception\TranslatorException;
use Comely\IO\Translator\Languages\Files;
use Comely\IO\Translator\Languages\Language;
use Comely\Kernel\Extend\ComponentInterface;

/**
 * Class Translator
 * @package Comely\IO\Translator
 * @property null|Directory $_dir
 * @property null|LanguageCacheInterface $_cache
 * @property array $_files
 * @property string $_filesCacheId
 */
class Translator implements ComponentInterface
{
    /** @var self */
    private static $instance;

    /** @var Directory */
    private $directory;
    /** @var Files */
    private $files;
    /** @var Languages */
    private $languages;
    /** @var null|LanguageCacheInterface */
    private $cache;
    /** @var null|string|Language */
    private $current;
    /** @var null|string|Language */
    private $fallback;

    /**
     * @return Translator
     */
    public static function getInstance(): self
    {
        if (self::$instance) {
            return self::$instance;
        }

        self::$instance = new self();
        return self::$instance;
    }

    /**
     * Translations constructor.
     */
    private function __construct()
    {
        $this->files = new Files();
        $this->languages = new Languages($this);
    }

    /**
     * @param Directory $directory
     * @return Translator
     */
    public function cacheDirectory(Directory $directory): self
    {
        $this->cache = new DiskCache($directory);
        return $this;
    }

    /**
     * @param $prop
     * @return array|string|Directory|LanguageCacheInterface|null
     * @throws TranslatorException
     */
    public function __get($prop)
    {
        switch ($prop) {
            case "_dir":
                return $this->directory;
            case "_cache":
                return $this->cache;
            case "_files":
                return $this->files->_selected;
            case "_filesCacheId":
                return $this->files->_cacheId;
        }

        throw new TranslatorException('Cannot access inaccessible properties');
    }

    /**
     * @param $name
     * @param $value
     * @throws TranslatorException
     */
    public function __set($name, $value)
    {
        throw new TranslatorException('Cannot write inaccessible properties');
    }

    /**
     * @param Directory $directory
     * @return Translator
     */
    public function directory(Directory $directory): self
    {
        $this->directory = $directory;
        return $this;
    }

    /**
     * @return Files
     */
    public function load(): Files
    {
        $this->languages->reset(); // Unset all loaded languages
        $this->files->reset(); // Unset all marked files
        return $this->files;
    }

    /**
     * @param string $lang
     * @return Translator
     * @throws LanguageException
     */
    public function language(string $lang): self
    {
        $this->current = Languages::validateName($lang);;
        return $this;
    }

    /**
     * @return null|string
     */
    public function current(): ?string
    {
        $current = $this->current instanceof Language ? $this->current->name() : $this->current;
        if (!$current) {
            $current = $this->fallback instanceof Language ? $this->fallback->name() : $this->fallback;
        }

        return $current;
    }

    /**
     * @param string $lang
     * @return Translator
     * @throws LanguageException
     */
    public function fallback(string $lang): self
    {
        try {
            $name = Languages::validateName($lang);
        } catch (LanguageException $e) {
            throw new LanguageException('Invalid fallback language name');
        }

        $this->fallback = $name;
        return $this;
    }

    /**
     * @param null|string $lang
     * @return Language
     * @throws LanguageException
     */
    private function _language(?string $lang = null): Language
    {
        if ($lang) {
            return $this->languages->get($lang);
        }

        if ($this->current instanceof Language) {
            return $this->current;
        }

        if ($this->current) {
            $this->current = $this->languages->get($this->current);
            return $this->current;
        }

        throw new LanguageException('No default language has been set');
    }

    /**
     * @return Language|null
     * @throws LanguageException
     */
    private function _fallback(): ?Language
    {
        if ($this->fallback instanceof Language) {
            return $this->fallback;
        }

        if ($this->fallback) {
            $this->fallback = $this->languages->get($this->fallback);
            return $this->fallback;
        }

        return null;
    }

    /**
     * Return string translation or NULL (if translation is not found)
     * On failure to read/compile language files, an E_USER_WARNING will be triggered and NULL returned
     *
     * @param string $key
     * @param null|string $lang
     * @return null|string
     */
    public function translate(string $key, ?string $lang = null): ?string
    {
        try {
            // Validate key
            $key = strtolower($key);
            if (!preg_match('/^[a-z0-9\.\-\_]+$/', $key)) {
                throw new TranslatorException('Invalid translation key');
            }

            // Get current language
            $language = $this->_language($lang);
            $translation = $language->get($key);

            if ($translation) { // Translation found
                return $translation;
            }

            // Fallback
            try {
                $fallback = $this->_fallback();
            } catch (LanguageException $e) {
                throw new LanguageException('[Fallback] ' . $e->getMessage());
            }

            if ($fallback && $fallback->name() !== $language->name()) {
                $translation = $fallback->get($key);
                if ($translation) {
                    return $translation;
                }
            }
        } catch (TranslatorException $e) {
            trigger_error(sprintf('Translation error [%s]: %s', $key, $e->getMessage()), E_USER_WARNING);
        }

        return null;
    }
}