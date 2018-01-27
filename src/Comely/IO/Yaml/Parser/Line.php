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

namespace Comely\IO\Yaml\Parser;

/**
 * Class Line
 * @package Comely\IO\Yaml\Parser
 */
class Line {
    /** @var int */
    public $indent;
    /** @var int */
    public $number;
    /** @var string */
    public $value;

    /**
     * @param int $number
     */
    public function __construct(int $number = 0)
    {
        $this->number   =   $number;
    }

    /**
     * @param string $line
     */
    public function read(string $line)
    {
        $this->number++;
        $this->indent   =   strlen($line) - strlen(ltrim($line));
        $this->value    =   $line;
    }

    /**
     * @return bool
     */
    public function isBlank() : bool
    {
        return (empty($this->value) ||  ctype_space($this->value)) ? true : false;
    }

    /**
     * @return bool
     */
    public function hasKey() : bool
    {
        return (preg_match("/^(\s+)?[\w\_\-\.]+\:(.*)$/", $this->value)) ? true : false;
    }

    /**
     * Find first occurrence of # outside of quotes to strip inline comments
     */
    public function clearInlineComment()
    {
        $this->value    =   trim(preg_split("/(#)(?=(?:[^\"\']|[\"\'][^\"\']*[\"\'])*$)/", $this->value, 2)[0]);
    }

    /**
     * Trim any trailing or leading whitespaces
     */
    public function clean()
    {
        $this->value    =   trim($this->value);
    }
}