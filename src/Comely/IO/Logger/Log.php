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

namespace Comely\IO\Logger;

/**
 * Class Log
 * @package Comely\IO\Logger
 */
class Log
{
    /** @var string */
    private $name;
    /** @var array */
    private $data;
    /** @var string */
    private $text;
    /** @var int */
    private $level;
    /** @var int */
    private $timeStamp;

    /**
     * Log constructor.
     * @param string $name
     * @param int $level
     */
    public function __construct(string $name, int $level)
    {
        $this->name =   $name;
        $this->data =   [];
        $this->text =   "";
        $this->level    =   $level;
        $this->timeStamp    =   time();
    }

    /**
     * @param int $level
     * @return Log
     */
    public function setLevel(int $level) : self
    {
        $this->level    =   $level;
        return $this;
    }

    /**
     * @param string $line
     * @param string $eol
     * @return Log
     */
    public function prepend(string $line, string $eol = PHP_EOL) : self
    {
        $this->text =  $line . $eol . $this->text;
        return $this;
    }

    /**
     * @param string $line
     * @param string $eol
     * @return Log
     */
    public function append(string $line, string $eol = PHP_EOL) : self
    {
        $this->text .=  $line . $eol;
        return $this;
    }

    /**
     * @param array $data
     * @return Log
     */
    public function attachData(array $data) : self
    {
        $this->data =   array_merge($this->data, $data);
        return $this;
    }

    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getAttachedData() : array
    {
        return $this->data;
    }

    /**
     * @return string
     */
    public function getMessage() : string
    {
        return $this->text;
    }

    /**
     * @return int
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * @return int
     */
    public function getTimeStamp() : int
    {
        return $this->timeStamp;
    }
}