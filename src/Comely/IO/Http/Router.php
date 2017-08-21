<?php
/**
 * This file is part of Comely IO package.
 * https://github.com/comelyio/comely
 *
 * Copyright (c) 2016-2017 Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/comelyio/comely/blob/master/LICENSE
 */

declare(strict_types=1);

namespace Comely\IO\Http;

use Comely\IO\Http\Controllers\ControllerInterface;
use Comely\IO\Http\Exception\RouterException;
use Comely\IO\Toolkit\Strings;

/**
 * Class Router
 * @package Comely\IO\Http
 */
class Router
{
    /** @var self */
    private static $instance;

    /** @var string */
    private $controllersPath;
    /** @var string */
    private $controllersNamespace;
    /** @var array */
    private $controllersArgs;
    /** @var null|string */
    private $defaultController;
    /** @var array */
    private $ignorePathIndexes;
    /** @var array */
    private $routes;

    /**
     * @return Router
     */
    public static function getInstance() : self
    {
        if(!isset(self::$instance)) {
            self::$instance =   new self();
        }

        return self::$instance;
    }

    /**
     * Private Router constructor.
     */
    private function __construct()
    {
        $this->routes   =   [];
        $this->controllersArgs   =   [];
        $this->ignorePathIndexes    =   [];
    }

    /**
     * @param string $path
     * @param string $namespace
     * @return Router
     * @throws RouterException
     */
    public function setControllersBase(string $path, string $namespace) : self
    {
        // Path must have trailing separator
        $path   =   rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        // Check if path is indeed a directory
        if(!@is_dir($path)) {
            throw RouterException::badControllersPath($path);
        }

        // Set default controllers resolving info.
        $this->controllersPath  =   $path;
        $this->controllersNamespace =   rtrim($namespace, "\\") . "\\";

        // Chain
        return $this;
    }

    /**
     * All these arguments will be passed to constructors of controllers
     *
     * @param array ...$args
     * @return Router
     */
    public function setControllersArgs(...$args) : self
    {
        $this->controllersArgs   =   $args;
        return $this;
    }

    /**
     * @param string $controller
     * @return Router
     */
    public function setDefaultController(string $controller) : self
    {
        $this->defaultController    =   $controller;
        return $this;
    }

    /**
     * @param \int[] ...$indexes
     * @return Router
     */
    public function ignorePathIndex(int ...$indexes) : self
    {
        $this->ignorePathIndexes    =   $indexes;
        return $this;
    }

    /**
     * @param string $path
     * @param string $controller
     * @return Router
     * @throws RouterException
     */
    public function addRoute(string $path, string $controller) : self
    {
        // Filter path
        $path   =   $this->filterPath($path, "[]+.-?_\\*");
        if(!$path) {
            throw RouterException::badRoutingPath(__METHOD__, $path);
        }

        // Save route
        $this->routes[$path]    =   $controller;

        // Chair
        return $this;
    }

    /**
     * @param string $path
     * @return ControllerInterface
     * @throws RouterException
     */
    public function route(string $path) : ControllerInterface
    {
        $controllerClass    =   $this->resolvePath($path);
        if(!class_exists($controllerClass, true)) {
            // Try default controller if set
            if(!isset($this->defaultController)) {
                throw RouterException::controllerNotFound($controllerClass);
            }

            $controllerClass    =   $this->defaultController;
            if(!class_exists($controllerClass, true)) {
                throw RouterException::controllerNotFound($controllerClass);
            }
        }

        // Init controller in try/catch block
        try {
            $controller =   new $controllerClass(...$this->controllersArgs);
        } catch(\Throwable $t) {
            // Throw exception if there was an error while constructing controller
            throw RouterException::controllerInitFail($controllerClass, $t->getMessage());
        }

        // Make sure controller instance implements ControllerInterface
        if(!is_subclass_of($controllerClass, "Comely\\IO\\Http\\Controllers\\ControllerInterface")) {
            throw RouterException::badController($controllerClass);
        }

        // Return controller
        return $controller;
    }

    /**
     * @param string $path
     * @return string
     * @throws RouterException
     */
    private function resolvePath(string $path) : string
    {
        // Filter path
        $pathKey    =   $this->filterPath($path);

        // Check in predefined routes
        foreach($this->routes as $route => $controller) {
            if(preg_match(sprintf("~^%s$~", $route), $pathKey)) {
                // Match found
                return $controller;
            }
        }

        // Make sure default controllers base directory path is set
        if(!$this->controllersPath) {
            throw RouterException::controllersPathNull();
        }

        // Dynamically convert path to class name
        $pathIndex  =   -1;
        $controllerClass    =   array_map(
            function($piece) use(&$pathIndex) : string {
                $pathIndex++;
                if($piece   &&  !in_array($pathIndex, $this->ignorePathIndexes)) {
                    return \Comely::pascalCase($piece);
                }

                return "";
            },
            explode("/", trim(Strings::filter(strtolower($path), "an", false, "/_"), "/"))
        );

        $controllerClass    =   trim(implode("\\", $controllerClass), "\\");
        $controllerClass    =   preg_replace("/\\\{2,}/", "\\", $controllerClass);

        // Join PascalCased class name with default namespace preset
        $controllerClass    =   $this->controllersNamespace . $controllerClass;

        // Return name of class
        return $controllerClass;
    }

    /**
     * @param string $path
     * @param string $extra
     * @return string
     */
    private function filterPath(string $path, string $extra = "") : string
    {
        // Filter path
        //$path   =   explode("?", strtolower($path))[0];
        $path   =   trim(Strings::filter($path, "ad", false, "/-_" . $extra), "\\/");
        return ($path) ?  $path : "/";
    }
}