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

namespace Comely\IO\Translator\Languages;

/**
 * Class Language
 * @package Comely\IO\Translator\Languages
 */
class Language
{
    /** @var string */
    private $name;
    /** @var array */
    private $translations;

    /**
     * Language constructor.
     * @param string $name
     * @param array $translations
     */
    public function __construct(string $name, array $translations)
    {
        $this->name = $name;
        $this->translations = [];
    }

    /**
     * @param array $translations
     * @param null|string $parent
     */
    private function feed(array $translations, ?string $parent = null): void
    {
        foreach ($translations as $key => $value) {
            // Validate key
            $key = trim(strtolower(sprintf('%s.%s', $parent, $key)), ".-_"); // Trim special chars from start/end
            if (!preg_match('^[a-z0-9\.\-\_]+$/', $key)) {
                $this->compileError(sprintf('Invalid translation key in parent "%s"', $parent ?? "~"));
                continue;
            }

            if (is_string($value)) {
                $this->translations[$key] = $value;
            } elseif (is_scalar($value)) {
                $this->translations[$key] = strval($value);
            } elseif (is_array($value)) {
                $this->feed($value, $key);
            } else {
                $this->compileError(sprintf('Invalid translation value for key "%s"', $key));
            }
        }
    }

    /**
     * @param string $message
     */
    private function compileError(string $message): void
    {
        trigger_error(sprintf('Language [%s]: %s', $this->name, $message), E_USER_NOTICE);
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @param string $key
     * @return null|string
     */
    public function get(string $key): ?string
    {
        return $this->translations[$key] ?? null;
    }
}