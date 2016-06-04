<?php
declare(strict_types=1);

namespace Comely\IO\Yaml\Parser;

/**
 * Class Line
 * This is a reusable object,
 * It should be instantiated once per IO\Yaml\Parse::parseYaml() method call
 * @package Comely\IO\Yaml\Parse
 */
class Line {
    public $indent;
    public $number;
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
        return (preg_match("/^(\s+)?[\w\_]+\:(.*)$/", $this->value)) ? true : false;
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