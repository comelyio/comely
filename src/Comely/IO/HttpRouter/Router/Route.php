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
 * @property string $_uri
 * @property string $_route
 * @property null|Controller $_default
 * @property null|Authentication $_auth
 */
class Route
{
    /** @var string */
    private $uri;
    /** @var string */
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
        if (!preg_match('/^\/' . $this->patternValidChars('/*') . '*$/', $uri)) {
            if (substr($uri, 0, 1) !== "/") {
                throw new RouteException('All HTTP routes must start with "/"');
            }

            throw new RouteException('HTTP route URI contain an illegal character');
        }

        $this->uri = preg_quote($uri, '/');
        $this->uri = strtolower($this->uri); // Case-insensitivity
        $this->uri = $this->wildcards($this->uri); // Activate wildcards

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
     * @param string $allow
     * @return string
     */
    private function patternValidChars(?string $allow = null): string
    {
        $allow = $allow ? preg_quote($allow, '/') : '';
        return sprintf('[a-zA-Z0-9\.\_\-%s]', $allow);
    }

    /**
     * @param string $uri
     * @return string
     */
    private function wildcards(string $uri): string
    {
        // Check if master wildcard exists
        $hasMasterWildcard = false;
        if (substr($uri, -4) === '\/\*') {
            $uri = substr($uri, 0, -4);
            $hasMasterWildcard = true;
        }

        // Activate remaining wildcard
        $uri = str_replace('\*', '[^\/]?[a-zA-Z0-9\.\_\-]*', $uri);

        // Add master wildcard
        if ($hasMasterWildcard) {
            $uri .= '[^\/]?[a-zA-Z0-9\.\_\-\/]*';
        }

        // All wildcards activated, return URI
        return $uri;
    }

    /**
     * @param $prop
     * @return bool|Controller|Authentication|mixed|null|string
     */
    public function __get($prop)
    {
        switch ($prop) {
            case "_uri":
                return $this->uri;
            case "_route":
                return $this->routeTo;
            case "_default":
                return $this->defaultController;
            case "_auth":
                return $this->authentication;
        }
        return false;
    }

    /**
     * @param $prop
     * @param $value
     * @return bool
     */
    public function __set($prop, $value)
    {
        return false; // Prevent override
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