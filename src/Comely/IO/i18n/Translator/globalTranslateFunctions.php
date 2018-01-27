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

// Global Namespace
namespace
{
    use Comely\IO\i18n\Translator;

    /**
     * Global translate function # 1
     * This function returns translation
     *
     * @param string $key
     * @return string|null
     */
    function __(string $key)
    {
        return Translator::getInstance()->translate($key);
    }

    /**
     * Global translate function # 2
     * Returns translation key as-is instead of NULL if a translation was not found
     *
     * @param string $key
     * @return string
     */
    function __k(string $key) : string
    {
        return Translator::getInstance()->translate($key) ?? $key;
    }

    /**
     * Global translate function # 3
     * This function returns a formatted translated String using vsprintf
     * or a Boolean false on failure
     *
     * @param string $key
     * @param array $args
     * @return string|bool
     */
    function __f(string $key, array $args)
    {
        $translated =   Translator::getInstance()->translate($key);
        if(is_string($translated)   &&  is_array($args)) {
            return vsprintf($translated, $args);
        }

         return false;
    }

    /**
     * Global translate function # 4
     * This function performs print OR vprintf on a translated String
     *
     * @param string $key
     * @param array|null $args
     */
    function __p(string $key, array $args = null)
    {
        $translated =   Translator::getInstance()->translate($key);
        if(is_string($translated)) {
            if(is_array($args)) {
                vprintf($translated, $args);
            } else {
                print($translated);
            }
        }
    }
}