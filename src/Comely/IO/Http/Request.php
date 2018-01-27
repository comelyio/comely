<?php
/**
 * This file is part of Comely IO package.
 * https://github.com/comelyio/comely
 *
 * Copyright (c) 2016-2018 Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/comelyio/comely/blob/master/LICENSE
 */

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
    /** @var ControllerInterface */
    private $controller;
    /** @var Input */
    private $input;
    /** @var string */
    private $method;
    /** @var Response */
    private $response;
    /** @var array */
    private $uri;

    /**
     * Request constructor.
     * @param string $method
     * @param string $uri
     * @param Input $input
     * @param callable|null $callback
     */
    public function __construct(string $method, string $uri, Input $input, callable $callback = null)
    {
        // Get Router instance
        $router =   Router::getInstance();

        // Method must be from GET, POST, PUT or DELETE
        $method =   strtoupper($method);
        if(!in_array($method, ["GET","POST","PUT","DELETE"])) {
            RequestException::badMethod($method);
        }

        // Resolve $uri to controller's instance
        $controller =   $router->route($uri);

        // Save request information
        $this->controller   =   $controller;
        $this->input    =   $input;
        $this->method   =   $method;
        $this->uri =   explode("/", trim($uri, "/"));
        $this->response =   new Response($this);

        // Call init method of Controller
        call_user_func_array([$controller,"init"], [$this,$this->response]);

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
     * @return string
     */
    public function getUri() : string
    {
        return implode("/", $this->uri);
    }

    /**
     * @param int $index
     * @return null
     */
    public function getUriIndex(int $index)
    {
        return $this->uri[$index] ?? null;
    }

    /**
     * @return string
     */
    public function getUriRoot() : string
    {
        return str_repeat("../", count($this->uri));
    }

    /**
     * @return Response
     */
    public function getResponse() : Response
    {
        return $this->response;
    }
}