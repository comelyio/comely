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

use Comely\IO\HttpRouter\Controller\Request\Headers;
use Comely\IO\HttpRouter\Controller\Request\Payload;
use Comely\IO\HttpRouter\Exception\RequestException;

/**
 * Class Request
 * @package Comely\IO\HttpRouter\Controller
 * @property string $_method
 * @property string $_uri
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
    /** @var Payload */
    private $payload;
    /** @var Headers */
    private $headers;

    /**
     * Request constructor.
     * @param string $method
     * @param string $uri
     * @param Payload $payload
     * @param Headers $headers
     * @throws RequestException
     */
    public function __construct(string $method, string $uri, Payload $payload, Headers $headers)
    {
        // Method
        $this->method = strtoupper($method);
        if (!in_array($this->method, ["GET", "POST", "PUT", "DELETE"])) {
            throw new RequestException('HTTP router will only accept GET/POST/PUT/DELETE requests');
        }

        // URI
        $this->uri = explode("?", $uri)[0]; // Strip GET query (if any)
        if (!preg_match('/^\/[a-zA-Z0-9\/\._\-\*]*$/', $this->uri)) {
            if (substr($this->uri, 0, 1) !== "/") {
                throw new RequestException('Request URI must begin with "/"');
            }

            throw new RequestException('Request URI contains an illegal character');
        }

        // URI Parts
        $this->uriParts = explode("/", trim($this->uri, "/"));

        // Payload and Headers
        $this->payload = $payload;
        $this->headers = $headers;
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