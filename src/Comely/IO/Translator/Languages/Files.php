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

namespace Comely\IO\Translator\Languages;

/**
 * Class Files
 * @package Comely\IO\Translator\Languages
 * @property array $_selected
 * @property string $_cacheId
 */
class Files
{
    /** @var array */
    private $selected;

    /**
     * Files constructor.
     */
    public function __construct()
    {
        $this->selected = [];
    }

    /**
     * @return Files
     */
    public function reset(): self
    {
        $this->selected = [];
        return $this;
    }

    /**
     * @param string $prop
     * @return array|bool|string
     */
    public function __get(string $prop)
    {
        ksort($this->selected);
        switch ($prop) {
            case "_selected":
                return array_keys($this->selected);
            case "_cacheId":
                return implode("", array_values($this->selected));
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
        $this->selected["dictionary"] = "dkn";
        return $this;
    }

    /**
     * @return Files
     */
    public function messages(): self
    {
        $this->selected["messages"] = "msg";
        return $this;
    }

    /**
     * @return Files
     */
    public function sitemap(): self
    {
        $this->selected["sitemap"] = "smp";
        return $this;
    }

    /**
     * @return Files
     */
    public function misc(): self
    {
        $this->selected["misc"] = "msc";
        return $this;
    }
}