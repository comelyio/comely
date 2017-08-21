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

/**
 * Interface LanguageInterface
 * @package Comely\IO\i18n\Translator
 */
interface LanguageInterface
{
    /**
     * Return name of language
     * @return string
     */
    public function name() : string;

    /**
     * Save a Key/Value pair as translation
     * 
     * @param string $key
     * @param string $value
     */
    public function set(string $key, string $value);

    /**
     *  Retrieve a translation using String key
     * 
     * @param string $key
     * @return string|null
     */
    public function get(string $key);
}