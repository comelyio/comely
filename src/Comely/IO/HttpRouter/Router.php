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

namespace Comely\IO\HttpRouter;

use Comely\IO\HttpRouter\Controller\Request;
use Comely\IO\HttpRouter\Controller\Request\Headers;
use Comely\IO\HttpRouter\Controller\Request\Payload;
use Comely\IO\HttpRouter\Exception\RoutingException;
use Comely\IO\HttpRouter\Router\Route;
use Comely\IO\HttpRouter\Router\Sanitizer;
use Comely\Kernel\Extend\ComponentInterface;

/**
 * Class Router
 * @package Comely\IO\HttpRouter
 */
class Router implements ComponentInterface
{
    /** @var array */
    private $routes;
    /** @var Sanitizer */
    private $sanitizer;

    /**
     * Router constructor.
     */
    public function __construct()
    {
        $this->routes = [];
        $this->sanitizer = new Sanitizer();
    }

    /**
     * @param string $uri
     * @param string $routeTo
     * @return Route
     */
    public function route(string $uri, string $routeTo): Route
    {
        $route = new Route($uri, $routeTo);
        $this->routes[] = $route;
        return $route;
    }

    /**
     * @param array $input
     * @return Payload
     */
    private function payload(array $input): Payload
    {
        return new Payload($this->sanitizer->payload($input));
    }

    /**
     * @param array $headers
     * @return Headers
     */
    private function headers(array $headers): Headers
    {
        return new Headers($this->sanitizer->headers($headers));
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array|null $payload
     * @param array|null $headers
     * @return Controller
     * @throws RoutingException
     */
    private function request(string $method, string $uri, ?array $payload = null, ?array $headers = null): Controller
    {

    }

    /**
     * @param string $uri
     * @param array|null $payload
     * @param array|null $headers
     * @return Controller
     * @throws RoutingException
     */
    public function get(string $uri, ?array $payload = null, ?array $headers = null): Controller
    {
        return $this->request("GET", $uri, $payload, $headers);
    }

    /**
     * @param string $uri
     * @param array|null $payload
     * @param array|null $headers
     * @return Controller
     * @throws RoutingException
     */
    public function post(string $uri, ?array $payload = null, ?array $headers = null): Controller
    {
        return $this->request("POST", $uri, $payload, $headers);
    }

    /**
     * @param string $uri
     * @param array|null $payload
     * @param array|null $headers
     * @return Controller
     * @throws RoutingException
     */
    public function delete(string $uri, ?array $payload = null, ?array $headers = null): Controller
    {
        return $this->request("DELETE", $uri, $payload, $headers);
    }

    /**
     * @param string $uri
     * @param array|null $payload
     * @param array|null $headers
     * @return Controller
     * @throws RoutingException
     */
    public function put(string $uri, ?array $payload = null, ?array $headers = null): Controller
    {
        return $this->request("PUT", $uri, $payload, $headers);
    }
}