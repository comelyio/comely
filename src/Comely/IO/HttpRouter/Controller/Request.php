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

namespace Comely\IO\HttpRouter\Controller;

use Comely\IO\HttpRouter\Controller\Data\Headers;
use Comely\IO\HttpRouter\Controller\Data\Payload;
use Comely\IO\HttpRouter\Exception\RequestException;

/**
 * Class Request
 * @package Comely\IO\HttpRouter\Controller
 * @property string $_method
 * @property string $_uri
 * @property string $_queryString
 * @property string $_root
 */
class Request
{
    /** @var string */
    private $method;
    /** @var string */
    private $uri;
    /** @var array */
    private $uriParts;
    /** @var string */
    private $queryString;
    /** @var Payload */
    private $payload;
    /** @var Headers */
    private $headers;

    /**
     * Request constructor.
     * @param \Comely\IO\HttpRouter\Router\Request $request
     */
    public function __construct(\Comely\IO\HttpRouter\Router\Request $request)
    {
        $this->method = $request->_method;
        $this->uri = $request->_uri;
        $this->uriParts = explode("/", trim($this->uri, "/"));
        $this->queryString = $request->_queryString;
        $this->payload = $request->_payload;
        $this->headers = $request->_headers;
    }

    /**
     * @param $prop
     * @return bool|mixed
     */
    public function __get($prop)
    {
        switch ($prop) {
            case "_method":
                return $this->method;
            case "_uri":
                return $this->uri;
            case "_queryString":
                return $this->queryString;
            case "_root":
                return $this->root();
        }

        return false;
    }

    /**
     * @param $prop
     * @param $value
     * @throws RequestException
     */
    final public function __set($prop, $value)
    {
        throw new RequestException('Cannot override inaccessible properties');
    }

    /**
     * @return string
     */
    public function root(): string
    {
        return str_repeat("../", count($this->uriParts));
    }

    /**
     * @param int|null $index
     * @return null|string
     */
    public function uri(?int $index = null): ?string
    {
        if (is_null($index)) {
            return $this->uri;
        }

        return $this->uriParts[$index] ?? null;
    }

    /**
     * @return Payload
     */
    public function payload(): Payload
    {
        return $this->payload;
    }

    /**
     * @return Payload
     */
    public function input(): Payload
    {
        return $this->payload();
    }

    /**
     * @return Headers
     */
    public function headers(): Headers
    {
        return $this->headers;
    }
}