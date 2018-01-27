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