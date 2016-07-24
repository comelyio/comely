<?php
declare(strict_types=1);

namespace Comely\IO\Http\Controllers;

use Comely\IO\Http\Request;

/**
 * Class AbstractRestController
 * @package Comely\IO\Http\Controllers
 */
abstract class AbstractRestController implements ControllerInterface
{
    const REST_METHOD_PARAM =   "action";

    protected $controller;
    protected $input;
    protected $method;
    protected $request;
    protected $response;

    /**
     * @param Request $request
     */
    public function init(Request $request)
    {
        // Save all request related information
        $this->request  =   $request;
        $this->controller   =   $request->getController();
        $this->input    =   $request->getInput();
        $this->method   =   $request->getMethod();
        $this->response =   $request->getResponse();

        // Get input params
        $params  =   $this->input->getData();

        try {
            if($this->method    === "GET") {
                // Single method for all get requests
                $callMethod =   "getView";
            } else {
                // Check if we have necessary param to build method name
                if(!array_key_exists(self::REST_METHOD_PARAM, $params)) {
                    throw new \Exception(
                        sprintf('Http requests must have required parameter "%s"', self::REST_METHOD_PARAM)
                    );
                }

                // Method name
                $callMethod =   $this->method . "_" . $params[self::REST_METHOD_PARAM];
                $callMethod =   \Comely::camelCase($callMethod);
            }

            // Check if method exists
            if(!method_exists($this, $callMethod)) {
                throw new \Exception('Request method not found');
            }

            // Call method
            call_user_func([$this,$callMethod]);
        } catch(\Throwable $t) {
            $this->response->set("error", $t->getMessage());
        }

        // Send response
        $this->response->send();
    }
}