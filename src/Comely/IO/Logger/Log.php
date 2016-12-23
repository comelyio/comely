<?php
declare(strict_types=1);

namespace Comely\IO\Logger;

/**
 * Class Log
 * @package Comely\IO\Logger
 */
class Log
{
    /** @var string */
    public $name;
    /** @var array */
    public $data;
    /** @var string */
    public $text;
    /** @var int */
    public $timeStamp;

    /**
     * Log constructor.
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name =   $name;
        $this->data =   [];
        $this->text =   "";
        $this->timeStamp    =   time();
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
}