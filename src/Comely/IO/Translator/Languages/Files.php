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
 * Class Files
 * @package Comely\IO\Translator\Languages
 * @property array $_selected
 */
class Files
{
    /** @var array */
    private $selected;

    /**
     * @param string $prop
     * @return array|bool
     */
    public function __get(string $prop)
    {
        if (strtolower($prop) === "_selected") {
            return array_keys($this->selected);
        }

        return false;
    }

    /**
     * @param $prop
     * @param $value
     * @return bool
     */
    public function __set($prop, $value)
    {
        return false;
    }

    /**
     * @return Files
     */
    public function dictionary(): self
    {
        $this->selected["dictionary"] = 1;
        return $this;
    }

    /**
     * @return Files
     */
    public function messages(): self
    {
        $this->selected["messages"] = 1;
        return $this;
    }

    /**
     * @return Files
     */
    public function sitemap(): self
    {
        $this->selected["sitemap"] = 1;
        return $this;
    }

    /**
     * @return Files
     */
    public function misc(): self
    {
        $this->selected["misc"] = 1;
        return $this;
    }
}