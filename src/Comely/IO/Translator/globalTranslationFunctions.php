<?php
/**
 * This file is part of Comely package.
 * https://github.com/comelyio/comely
 *
 * Copyright (c) 2016-2019 Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/comelyio/comely/blob/master/LICENSE
 */

declare(strict_types=1);

namespace {

    use Comely\IO\Translator\Translator;

    if (!function_exists("__")) {
        /**
         * Global translate function # 1
         * Retrieves a translation against $key
         *
         * @param string $key
         * @param null|string $lang If NULL, currently set Language instance will be used
         * @return null|string
         */
        function __(string $key, ?string $lang = null): ?string
        {
            return Translator::getInstance()->translate($key, $lang);
        }
    }

    if (!function_exists("__k")) {
        /**
         * Global translate function # 2
         * Retrieves translation, return $key is no translation was found
         *
         * @param string $key
         * @param null|string $lang If NULL, currently set Language instance will be used
         * @return string
         */
        function __k(string $key, ?string $lang = null): string
        {
            return __($key, $lang) ?? $key;
        }
    }

    if (!function_exists("__f")) {
        /**
         * Global translate function # 3
         * Retrieves translation, format using vsprintf
         *
         * @param string $key
         * @param array $args
         * @param null|string $lang
         * @return null|string
         */
        function __f(string $key, array $args, ?string $lang = null): ?string
        {
            $translation = __($key, $lang);
            if ($translation) {
                $formatted = vsprintf($translation, $args);
                if ($formatted) {
                    return $formatted;
                }
            }

            return null;
        }
    }
}