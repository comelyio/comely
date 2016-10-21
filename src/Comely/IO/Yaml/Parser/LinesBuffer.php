<?php
declare(strict_types=1);

namespace Comely\IO\Yaml\Parser;

/**
 * Class Buffer
 * Buffers lines to be parsed in IO\Yaml\Parse::parseYaml() method
 * @package Comely\IO\Yaml\Parser
 */
class LinesBuffer
{
    /** @var string */
    private $type;
    /** @var null|string */
    private $key;
    /** @var int */
    private $indent;
    /** @var int */
    private $linesOffset;
    /** @var array */
    private $lines;
    /** @var null|LinesBuffer */
    private $subBuffer;

    /**
     * LinesBuffer constructor.
     * @param string|null $key
     * @param int $indent
     * @param int $linesOffset
     */
    public function __construct(string $key = null, int $indent = 0, int $linesOffset = 0)
    {
        $this->type =   "~";
        $this->key  =   $key;
        $this->indent   =   $indent;
        $this->linesOffset  =   $linesOffset;
        $this->lines    =   [];
    }

    /**
     * Bootstrap initial buffer
     * @param array $lines
     * @return LinesBuffer
     */
    public function bootstrap(array $lines) : self
    {
        $this->lines    =   $lines;
        return $this;
    }

    /**
     * Set buffering type
     * @param string $type
     */
    public function setType(string $type)
    {
        $this->type =   $type[0];
    }

    /**
     * @return string
     */
    public function getType() : string
    {
        return $this->type;
    }

    /**
     * @return null|string
     */
    public function getKey()
    {
       return $this->key;
    }

    /**
     * @return bool
     */
    public function hasKey() : bool
    {
        return (!empty($this->key)) ? true : false;
    }

    /**
     * @return array
     */
    public function getBufferedData() : array
    {
        return $this->lines;
    }

    /**
     * @return int
     */
    public function getIndent() : int
    {
        return $this->indent;
    }

    /**
     * @return int
     */
    public function getLinesOffset() : int
    {
        return $this->linesOffset;
    }

    /**
     * Add new line to sub buffer (if exists), or this buffer
     * @param string $line
     */
    public function addToBuffer(string $line)
    {
        if($this->isBuffering()) {
            $this->subBuffer->addToBuffer($line);
        } else {
            $this->lines[]  =   $line;
        }
    }

    /**
     * @param string $key
     * @param int $indent
     * @param int $linesOffset
     * @return LinesBuffer
     */
    public function createSubBuffer(string $key, int $indent, int $linesOffset) : self
    {
        $this->subBuffer    =   new LinesBuffer($key, $indent, $linesOffset);
        return $this->subBuffer;
    }

    /**
     * Check if a sub buffer has been created using createSubBuffer method
     * @return bool
     */
    public function isBuffering() : bool
    {
        return (is_object($this->subBuffer) &&  $this->subBuffer instanceof LinesBuffer) ? true : false;
    }

    /**
     * @return LinesBuffer|null
     */
    public function getSubBuffer()
    {
        return ($this->isBuffering()) ? $this->subBuffer : null;
    }

    /**
     * Clear sub buffering
     */
    public function clearSubBuffer()
    {
        $this->subBuffer    =   null;
    }
}