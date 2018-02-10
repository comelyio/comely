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
use Comely\IO\HttpRouter\Exception\RouteException;

/**
 * Class Route
 * @package Comely\IO\HttpRouter\Router
 */
class Route
{
    /** @var mixed */
    private $uri;
    /** @var bool|string */
    private $routeTo;
    /** @var null|Controller */
    private $defaultController;
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
        if (!preg_match('/^\/[a-zA-Z0-9' . preg_quote('/._-*', '/') . ']*$/', $uri)) {
            if (substr($uri, 0, 1) !== "/") {
                throw new RouteException('All HTTP routes must start with "/"');
            }

            throw new RouteException('HTTP route URI contain an illegal character');
        }

        $this->uri = preg_quote($uri, '/');
        $this->uri = strtolower($this->uri); // Case-insensitivity
        $this->uri = str_replace('\*', '.*', $this->uri); // Activate wildcards

        // Validate Controller/Namespace
        if (!preg_match('/^[a-zA-Z0-9\_]+(\\\[a-zA-Z0-9\_]+)*$/', $routeTo)) {
            throw new RouteException('Invalid route Controller class or Namespace');
        }

        $this->routeTo = $routeTo;
        // Remove trailing backslash from Namespace
        if (ord($this->routeTo[-1]) === 92) {
            $this->routeTo = substr($routeTo, 0, -1);
        }
    }

    /**
     * @param Controller $controller
     * @return Route
     */
    public function default(Controller $controller): self
    {
        $this->defaultController = $controller;
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