<?php
declare(strict_types=1);

namespace Comely\IO\Http;

use Comely\IO\Http\Controllers\ControllerInterface;
use Comely\IO\Http\Exception\RequestException;
use Comely\IO\Http\Request\Input;
use Comely\IO\Http\Request\Response;

/**
 * Class Request
 * @package Comely\IO\Http
 */
class Request
{
    private $controller;
    private $input;
    private $method;
    private $path;
    private $response;

    public function __construct(string $method, string $path, Input $input, callable $callback = null)
    {
        // Get Router instance
        $router =   Router::getInstance();

        // Method must be from GET, POST, PUT or DELETE
        $method =   strtoupper($method);
        if(!in_array($method, ["GET","POST","PUT","DELETE"])) {
            RequestException::badMethod($method);
        }

        // Resolve $path to controller's instance
        $controller =   $router->route($path);

        // Save request information
        $this->controller   =   $controller;
        $this->input    =   $input;
        $this->method   =   $method;
        $this->path =   $path;
        $this->response =   new Response($this);

        // Call init method of Controller
        call_user_func([$controller,"init"]);

        // Callback
        if(isset($callback)) {
            call_user_func_array($callback, [$this, $this->response]);
        }
    }

    /**
     * @return ControllerInterface
     */
    public function getController(): ControllerInterface
    {
        return $this->controller;
    }

    /**
     * @return Input
     */
    public function getInput() : Input
    {
        return $this->input;
    }

    /**
     * @return string
     */
    public function getMethod() : string
    {
        return $this->method;
    }

    /**
     * @return Response
     */
    public function getResponse() : Response
    {
        return $this->response;
    }
}