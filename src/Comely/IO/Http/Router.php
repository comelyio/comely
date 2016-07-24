<?php
declare(strict_types=1);

namespace Comely\IO\Http;

use Comely\IO\Http\Controllers\ControllerInterface;
use Comely\IO\Http\Exception\RouterException;
use Comely\IO\Toolkit\String\Strings;

/**
 * Class Router
 * @package Comely\IO\Http
 */
class Router
{
    private static $instance;

    private $controllersPath;
    private $controllersNamespace;
    private $controllersArgs;
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
    }

    /**
     * @param string $path
     * @param string $namespace
     * @return Router
     * @throws RouterException
     */
    public function setDefaultControllers(string $path, string $namespace) : self
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
    public function setControllerArgs(...$args) : self
    {
        $this->controllersArgs   =   $args;
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
        $path   =   $this->filterPath($path);
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
            throw RouterException::controllerNotFound($controllerClass);
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
        $pathKey    =   $this->filterPath($path);
        if(array_key_exists($pathKey, $this->routes)) {
            // Predefined routes
            $controllerClass    =   $this->routes[$pathKey];
        } else {
            // Make sure default controllers base directory path is set
            if(!$this->controllersPath) {
                throw RouterException::controllersPathNull();
            }

            // Dynamically convert path to class name
            $controllerClass    =   array_map(
                function($piece) {
                    return \Comely::pascalCase($piece);
                },
                explode("/", trim(Strings::filter(strtolower($path), "an", false, "/")))
            );

            // Join PascalCased class name with default namespace preset
            $controllerClass    =   $this->controllersNamespace . implode("\\", $controllerClass);
        }

        // Return name of class
        return $controllerClass;
    }

    /**
     * @param string $path
     * @return string
     */
    private function filterPath(string $path) : string
    {
        // Filter path
        return trim(Strings::filter(strtolower($path), "ad", false, "/-_+"));
    }
}