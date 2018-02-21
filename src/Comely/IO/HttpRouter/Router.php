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
use Comely\IO\HttpRouter\Exception\RoutingException;
use Comely\IO\HttpRouter\Router\Request;
use Comely\IO\HttpRouter\Router\Response;
use Comely\IO\HttpRouter\Router\Route;
use Comely\IO\HttpRouter\Router\Sanitizer;
use Comely\Kernel\Extend\ComponentInterface;

/**
 * Class Router
 * @package Comely\IO\HttpRouter
 * @property null|string $_fallback
 */
class Router implements ComponentInterface
{
    /** @var array */
    private $routes;
    /** @var Sanitizer */
    private $sanitizer;
    /** @var null|string */
    private $fallbackController;
    /** @var Response */
    private $response;

    /**
     * Router constructor.
     */
    public function __construct()
    {
        $this->routes = [];
        $this->sanitizer = new Sanitizer();
        $this->response = new Response();
    }

    /**
     * @param $prop
     * @return null|string
     * @throws RoutingException
     */
    public function __get(string $prop)
    {
        switch ($prop) {
            case "_fallback":
                return $this->fallbackController;
        }

        throw new RoutingException('Cannot access inaccessible properties');
    }

    /**
     * @param $prop
     * @param $value
     * @throws RoutingException
     */
    final public function __set(string $prop, $value)
    {
        throw new RoutingException('Cannot override inaccessible properties');
    }

    /**
     * @param string $controller
     * @return Router
     * @throws RoutingException
     */
    public function fallbackController(string $controller): self
    {
        // Validate Controller
        if (!preg_match('/^[a-zA-Z0-9\_]+(\\\[a-zA-Z0-9\_]+)*$/', $controller)) {
            throw new RoutingException('Invalid fallback controller name');
        }

        // Make sure that class exists
        if (!class_exists($controller)) {
            throw new RoutingException(sprintf('Fallback controller class "%s" not found', $controller));
        }

        $this->fallbackController = $controller;
        return $this;
    }

    /**
     * @return Response
     */
    public function response(): Response
    {
        return $this->response;
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

    /**
     * @param Request $request
     * @return Controller
     * @throws RoutingException
     */
    public function send(Request $request): Controller
    {
        $controller = null;
        /** @var Route $route */
        foreach ($this->routes as $route) {
            $controller = $route->request($request);
            if ($controller) {
                break;
            }
        }

        // Get routed or fallback controller name
        $controller = $controller ?? $this->fallbackController ?? null;
        if (!$controller) {
            throw new RoutingException('Failed to route request to any HTTP controller');
        }

        // Make sure class exists
        if (!class_exists($controller)) {
            throw new RoutingException(sprintf('Request routed to "%s" HTTP controller not found', $controller));
        }

        // Make sure that class is instance of HTTP controller before it is constructed
        $reflect = new \ReflectionClass($controller);
        if (!$reflect->isSubclassOf('Comely\IO\HttpRouter\Controller')) {
            throw new RoutingException(
                sprintf('Request routed to "%s" but object is not an instance of HTTP controller', $controller)
            );
        }

        // Bootstrap HTTP Controller
        $controller = new $controller($this, new Controller\Request($request));

        return $controller;
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