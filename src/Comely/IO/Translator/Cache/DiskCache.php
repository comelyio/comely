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

namespace Comely\IO\Translator\Cache;

use Comely\IO\FileSystem\Disk\Directory;
use Comely\IO\FileSystem\Exception\DiskException;
use Comely\IO\Translator\Exception\CachedLanguageException;
use Comely\IO\Translator\Exception\TranslatorException;
use Comely\IO\Translator\Languages\Language;

/**
 * Class DiskCache
 * @package Comely\IO\Translator\Cache
 */
class DiskCache implements LanguageCacheInterface
{
    /** @var Directory */
    private $directory;

    /**
     * DiskCache constructor.
     * @param Directory $directory
     */
    public function __construct(Directory $directory)
    {
        if (!$directory->permissions()->read) {
            throw new TranslatorException('Cache directory must have READ permission');
        } elseif (!$directory->permissions()->write) {
            throw new TranslatorException('Cache directory must have WRITE permission');
        }

        $this->directory = $directory;
    }

    /**
     * @param string $name
     * @param string $group
     * @return Language|null
     * @throws CachedLanguageException
     */
    public function get(string $name, string $group): ?Language
    {
        try {
            $cachedFile = $this->directory->file(sprintf('lang.%s.%s.php.cache', $name, $group));
        } catch (DiskException $e) {
            // File no exists, no error to give
            return null;
        }

        // Cached file found, attempt to read
        try {
            $serialized = $cachedFile->read();
        } catch (DiskException $e) {
            throw new CachedLanguageException(
                sprintf('Failed to read cached language file "%s"', basename($cachedFile->path()))
            );
        }

        // Unserialize
        try {
            $language = unserialize($serialized, [
                "allowed_classes" => [
                    'Comely\IO\Translator\Languages\Language'
                ]
            ]);
        } catch (CachedLanguageException $e) {
        }

        if (!isset($language) || !$language instanceof Language) {
            throw new CachedLanguageException(
                sprintf('Cached language file "%s" is incomplete or corrupted', basename($cachedFile->path()))
            );
        }

        return $language;
    }

    /**
     * @param Language $language
     * @throws CachedLanguageException
     */
    public function store(Language $language): void
    {
        $cacheFile = sprintf('lang.%s.%s.php.cache', $language->name(), $language->group());
        try {
            $this->directory->write($cacheFile, serialize($language), false, true);
        } catch (DiskException $e) {
            throw new CachedLanguageException(
                sprintf('Failed to store "%s" in cache directory', $cacheFile)
            );
        }
    }
}