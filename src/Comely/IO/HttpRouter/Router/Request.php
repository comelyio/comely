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

namespace Comely\IO\HttpRouter\Router;

use Comely\IO\HttpRouter\Controller;
use Comely\IO\HttpRouter\Controller\Data\Payload;
use Comely\IO\HttpRouter\Controller\Data\Headers;
use Comely\IO\HttpRouter\Exception\RequestException;
use Comely\IO\HttpRouter\Exception\RoutingException;
use Comely\IO\HttpRouter\Router;

/**
 * Class Request
 * @package Comely\IO\HttpRouter\Router
 * @property string $_method
 * @property string $_uri
 * @property string $_queryString
 * @property Payload $_payload
 * @property Headers $_headers
 */
class Request
{
    /** @var Router */
    private $router;
    /** @var string */
    private $method;
    /** @var string */
    private $uri;
    /** @var string */
    private $queryString;
    /** @var Payload */
    private $payload;
    /** @var Headers */
    private $headers;

    /**
     * Request constructor.
     * @param Router $router
     * @param string $method
     * @param string $uri
     * @throws RequestException
     */
    public function __construct(Router $router, string $method, string $uri)
    {
        // Method
        $this->method = strtoupper($method);
        if (!in_array($this->method, ["GET", "POST", "PUT", "DELETE"])) {
            throw new RequestException('HTTP router will only accept GET/POST/PUT/DELETE requests');
        }

        // URI
        $uri = explode("?", $uri); // Strip GET query string (if any)
        $this->uri = $uri[0];
        unset($uri[0]);
        $this->queryString = implode("?", $uri);

        // Validate URI
        if (!preg_match('/^\/[a-zA-Z0-9\/\._\-\*]*$/', $this->uri)) {
            if (substr($this->uri, 0, 1) !== "/") {
                throw new RequestException('Request URI must begin with "/"');
            }

            throw new RequestException('Request URI contains an illegal character');
        }

        $this->router = $router;
        $this->method = $method;
        $this->payload = new Payload($router);
        $this->headers = new Headers($router);
    }

    /**
     * @param $prop
     * @return bool|Headers|Payload|string
     */
    public function __get($prop)
    {
        switch ($prop) {
            case "_method":
                return $this->method;
            case "_uri":
                return $this->uri;
            case "_payload":
                return $this->payload;
            case "_headers":
                return $this->headers;
            case "_queryString":
                return $this->queryString;
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
     * @param Headers|null $headers
     * @return Headers
     */
    public function headers(?Headers $headers = null): Headers
    {
        $this->headers = $headers ?? $this->headers;
        return $this->headers;
    }

    /**
     * @param array $payload
     * @return Request
     */
    public function payload(array $payload): self
    {
        $this->payload = new Payload($this->router, $payload);
        return $this;
    }

    /**
     * @return Controller
     * @throws RoutingException
     */
    public function send(): Controller
    {
        return $this->router->send($this);
    }
}