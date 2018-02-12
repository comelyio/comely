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

use Comely\IO\Translator\Exception\CachedLanguageException;
use Comely\IO\Translator\Languages\Language;

/**
 * Interface LanguageCacheInterface
 * @package Comely\IO\Translator\Cache
 */
interface LanguageCacheInterface
{
    /**
     * @param string $name
     * @param string $group
     * @return Language|null
     * @throws CachedLanguageException
     */
    public function get(string $name, string $group): ?Language;

    /**
     * @param Language $language
     * @throws CachedLanguageException
     */
    public function store(Language $language): void;
}