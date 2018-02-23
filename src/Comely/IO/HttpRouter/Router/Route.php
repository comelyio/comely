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

use Comely\IO\HttpRouter\Authentication;
use Comely\IO\HttpRouter\Exception\RouteException;
use Comely\IO\HttpRouter\Exception\RoutingException;
use Comely\Kernel\Comely;

/**
 * Class Route
 * @package Comely\IO\HttpRouter\Router
 */
class Route
{
    private const ROUTE_NAMESPACE = 200;
    private const ROUTE_DIRECT = 100;

    /** @var string */
    private $uri;
    /** @var string */
    private $route;
    /** @var int */
    private $routeType;
    /** @var null|string */
    private $fallbackController;
    /** @var null|Authentication */
    private $authentication;

    /**
     * Route constructor.
     * @param string $uri
     * @param string $routeTo
     */
    public function __construct(string $uri, string $routeTo)
    {
        // Validate URI
        if (!preg_match('/^\/[a-zA-Z0-9\.\_\-\*]+(\/?[a-zA-Z0-9\.\_\-\*]+)*$/', $uri)) {
            if (substr($uri, 0, 1) !== "/") {
                throw new RouteException('All HTTP routes must start with "/"');
            } elseif (substr($uri, -1) === "/") {
                throw new RouteException('HTTP route cannot end with "/", use "/*" for wildcard');
            }

            throw new RouteException('HTTP route URI contain an illegal character');
        }

        // Validate Controller/Namespace
        if (!preg_match('/^[a-zA-Z0-9\_]+(\\\[a-zA-Z0-9\_]+)*(\\\\\*)?$/', $routeTo)) {
            throw new RouteException('Invalid route Controller class or Namespace');
        }

        // Prepare URI pattern
        $this->uri = strtolower($uri); // Case-insensitivity
        $this->uri = preg_replace('/\*{2,}/', '*', $this->uri);

        // Controller/Namespace Path
        $this->route = $routeTo;

        // Check if route leads to a Namespace
        if (substr($this->route, -2) === '\*') {
            if (substr($this->uri, -2) !== '/*') {
                throw new RoutingException(
                    sprintf('URI for namespace "%s" must end with /*', $routeTo)
                );
            }

            // Route to Namespace
            $this->uri = $this->wildcards($this->uri);
            $this->routeType = self::ROUTE_NAMESPACE;
            $this->route = substr($this->route, 0, -2);
        } else {
            // Direct route to Controller
            $this->uri = $this->wildcards($this->uri);
            $this->routeType = self::ROUTE_DIRECT;
            if (!class_exists($this->route)) {
                throw new RoutingException(
                    sprintf(
                        'Cannot find controller "%s" for direct routing, for namespaces suffix path with "\\*"',
                        $this->route
                    )
                );
            }
        }
    }

    /**
     * @param string $uri
     * @return string
     */
    private function wildcards(string $uri): string
    {
        $uri = preg_quote($uri, '/');

        // Last wildcard
        if (substr($uri, -4) === '\/\*') {
            $uri = substr($uri, 0, -4) . '(\/[a-zA-Z0-9\.\_\-]+)*';
        }

        // Optional trailing "/"
        $uri .= "\/?";

        // Middle wildcards
        $uri = str_replace('\*', '[^\/]?[a-zA-Z0-9\.\_\-]*', $uri);

        return $uri; // activated
    }

    /**
     * @param Request $request
     * @return null|string
     */
    public function request(Request $request): ?string
    {
        // Prepare pattern
        $pattern = $this->uri;
        if ($this->routeType === self::ROUTE_NAMESPACE) {
            $pattern .= '[a-zA-Z0-9\.\_\-\/]*';
        }

        // Match with URI
        if (!preg_match('/^' . $pattern . '$/', $request->_uri)) {
            return null; // No match
        }

        // Request matches with this Route
        // Authentication?
        if ($this->authentication) {
            $this->authentication->authenticate(
                $request->headers()->get("authorization") // Get request header: Authorization
            );
        }

        // Find HTTP Controller
        $controller = null;
        if ($this->routeType === self::ROUTE_DIRECT) {
            $controller = $this->route; // Class exists check already done in constructor
        } elseif ($this->routeType === self::ROUTE_NAMESPACE) {
            $offset = [];
            preg_match('/^' . $this->uri . '/', $request->_uri, $offset);
            $offset = strlen(strval($offset[0] ?? ""));
            $controller = array_map(function ($part) {
                if ($part) {
                    return Comely::PascalCase($part);
                }
                return null;
            }, explode("/", substr($request->_uri, $offset)));
            $controller = sprintf('%s\%s', $this->route, implode('\\', $controller));
            $controller = preg_replace('/\\\{2,}/', '\\', $controller);
            $controller = rtrim($controller, '\\');
        }

        // Check controller class exist
        if (!$controller || !class_exists($controller)) {
            // Nope... Try using a fallback controller
            $controller = $this->fallbackController ?? null;
            if (!$controller || !class_exists($controller)) {
                return null;
            }
        }

        return $controller;
    }

    /**
     * @param string $controller
     * @return Route
     * @throws RouteException
     */
    public function fallbackController(string $controller): self
    {
        // Validate Controller
        if (!preg_match('/^[a-zA-Z0-9\_]+(\\\[a-zA-Z0-9\_]+)*$/', $controller)) {
            throw new RouteException('Invalid fallback controller name');
        }

        // Make sure that class exists
        if (!class_exists($controller)) {
            throw new RouteException(sprintf('Fallback controller class "%s" not found', $controller));
        }

        $this->fallbackController = $controller;
        return $this;
    }

    /**
     * @param Authentication $auth
     * @return Route
     */
    public function authenticate(Authentication $auth): self
    {
        $this->authentication = $auth;
        return $this;
    }
}