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

namespace Comely\IO\i18n\Translator;

/**
 * Class Language
 * @package Comely\IO\i18n\Translator
 */
class Language implements LanguageInterface
{
    /** @var string */
    private $name;
    /** @var array */
    private $translations;

    /**
     * Language constructor.
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name =   $name;
        $this->translations =   [];
    }

    /**
     * @return string
     */
    public function name() : string
    {
        return $this->name;
    }

    /**
     * @param string $key
     * @param string $value
     */
    public function set(string $key, string $value)
    {
        // Translation keys should be case-insensitive
        $key    =   strtolower($key);

        // Save translation
        $this->translations[$key]   =   $value;
    }

    /**
     * @param $key
     * @param $value
     */
    public function __set(string $key, string $value)
    {
        $this->set($key, $value);
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function get(string $key)
    {
        // Translation keys should be case-insensitive
        $key    =   strtolower($key);

        // Search in translations
        if(array_key_exists($key, $this->translations)) {
            // Translation found, return String
            return $this->translations[$key];
        } else {
            // Not found, NULL
            return null;
        }
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function __get(string $key)
    {
        return $this->get($key);
    }
}