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

use Comely\IO\HttpRouter\Exception\RequestException;
use Comely\IO\HttpRouter\Router\Request;
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
     * @return Sanitizer
     */
    public function sanitizer(): Sanitizer
    {
        return $this->sanitizer;
    }

    public function send(Request $request): Controller
    {

    }

    /**
     * @param string $method
     * @param string $uri
     * @return Request
     * @throws RequestException
     */
    private function request(string $method, string $uri): Request
    {
        return new Request($this, $method, $uri);
    }

    /**
     * @param string $uri
     * @return Request
     * @throws RequestException
     */
    public function get(string $uri): Request
    {
        return $this->request("GET", $uri);
    }

    /**
     * @param string $uri
     * @return Request
     * @throws RequestException
     */
    public function post(string $uri): Request
    {
        return $this->request("POST", $uri);
    }

    /**
     * @param string $uri
     * @return Request
     * @throws RequestException
     */
    public function put(string $uri): Request
    {
        return $this->request("PUT", $uri);
    }

    /**
     * @param string $uri
     * @return Request
     * @throws RequestException
     */
    public function delete(string $uri): Request
    {
        return $this->request("DELETE", $uri);
    }
}