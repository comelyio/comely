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

use Comely\IO\FileSystem\Disk\Directory;
use Comely\IO\FileSystem\Exception\DiskException;
use Comely\IO\Translator\Cache\LanguageCacheInterface;
use Comely\IO\Translator\Exception\CachedLanguageException;
use Comely\IO\Translator\Exception\CompileLanguageException;
use Comely\IO\Translator\Exception\LanguageException;
use Comely\IO\Translator\Languages\Language;
use Comely\IO\Yaml\Exception\YamlException;
use Comely\IO\Yaml\Yaml;

/**
 * Class Languages
 * @package Comely\IO\Translator
 */
class Languages
{
    /** @var Translator */
    private $translator;
    /** @var array */
    private $languages;

    /**
     * Languages constructor.
     * @param Translator $translator
     */
    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
        $this->languages = [];
    }

    /**
     * @param string $name
     * @return Language
     * @throws LanguageException
     */
    public function get(string $name): Language
    {
        // Validate Name
        $name = self::validateName($name);

        // Check if already complied
        if (isset($this->languages[$name])) {
            return $this->languages[$name];
        }

        // Check in cache
        try {
            $language = $this->cached($name);
        } catch (CachedLanguageException $e) {
        }

        // Compile
        if (!isset($language) || !$language instanceof Language) {
            $language = $this->compile($name);
        }

        $this->languages[$name] = $language;
        return $language;
    }

    /**
     * @param string $name
     * @return Language|null
     */
    private function cached(string $name): ?Language
    {
        $cacheStore = $this->translator->_cache;
        if (!$cacheStore instanceof LanguageCacheInterface) {
            return null;
        }

        try {
            return $cacheStore->get($name, $this->translator->_filesCacheId);
        } catch (CachedLanguageException $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);
        }

        return null;
    }

    /**
     * @param string $name
     * @return Language
     * @throws CompileLanguageException
     */
    private function compile(string $name): Language
    {
        $directories = $this->translator->_dir;
        if (!$directories instanceof Directory) {
            throw new CompileLanguageException('No translations directory has been defined');
        }

        try {
            // Language Directory
            $languageDirectory = $directories->dir($name);
        } catch (DiskException $e) {
            throw new CompileLanguageException(sprintf('Language directory "%s" not found', $name));
        }

        // Check read permissions
        if (!$languageDirectory->permissions()->read) {
            throw new CompileLanguageException(sprintf('Language directory "%s" is not readable', $name));
        }

        // Parse translations
        $translations = [];
        $count = 0;
        $group = $this->translator->_filesCacheId;
        foreach ($this->translator->_files as $file) {
            try {
                $parsed = Yaml::Parse($languageDirectory->suffixed($file . ".yml"))
                    ->setEOL("\n")
                    ->evaluateBooleans(false)
                    ->generate();

                array_push($translations, $parsed);
                $count++;
            } catch (YamlException $e) {
                throw new CompileLanguageException(
                    sprintf('Failed to parse "%s.yml" for language "%s"', $file, $name)
                );
            }
        }

        if (!$count || !$group) {
            throw new CompileLanguageException(sprintf('No files were loaded for language "%s"', $name));
        }

        // Construct Language instance
        $language = new Language($name, $group, $translations);

        // Cache storage?
        $cacheStore = $this->translator->_cache;
        if ($cacheStore) {
            try {
                $cacheStore->store($language);
            } catch (CachedLanguageException $e) {
                trigger_error($e->getMessage(), E_USER_WARNING);
            }
        }

        return $language;
    }

    /**
     * @return void
     */
    public function reset(): void
    {
        $this->languages = [];
    }

    /**
     * @param string $given
     * @return string
     * @throws LanguageException
     */
    public static function validateName(string $given): string
    {
        $name = strtolower($given);
        if (!preg_match('/^[a-z]{2}(\-[a-z]{2})?$/', $name)) {
            throw new LanguageException('Invalid language name');
        }

        return $name;
    }
}