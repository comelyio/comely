<?php
declare(strict_types=1);

namespace Comely\IO\Http\Request;

/**
 * Class Input
 * @package Comely\IO\Http\Request
 */
class Input
{
    /** @var array */
    private $data;
    /** @var array */
    private $headers;

    /**
     * InputData constructor.
     * @param array $data
     * @param array $headers
     */
    public function __construct(array $data, array $headers)
    {
        $this->data =   $data;
        $this->headers  =   $headers;
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    public function getHeader(string $name)
    {;
        return $this->headers[strtolower($name)] ?? null;
    }

    /**
     * @return array
     */
    public function getData() : array
    {
        return $this->data;
    }
}