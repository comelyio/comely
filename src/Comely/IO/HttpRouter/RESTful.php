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

use Comely\IO\HttpRouter\Exception\ControllerResponseException;
use Comely\IO\HttpRouter\Exception\RESTfulException;
use Comely\IO\HttpRouter\Router\Request;

/**
 * Class RESTful
 * @package Comely\IO\HttpRouter
 */
class RESTful
{
    /**
     * @param Router $router
     * @param \Closure|null $closure
     * @return Controller
     * @throws Exception\RoutingException
     * @throws RESTfulException
     */
    public static function Request(Router $router, ?\Closure $closure = null): Controller
    {
        $httpMethod = $_SERVER["REQUEST_METHOD"] ?? "";
        $httpUri = $_SERVER["REQUEST_URI"] ?? "/";

        // Check if URL not rewritten properly (i.e. called /index.php/some/controller)
        if (preg_match('/^\/?[a-z0-9\-\_\.]+\.php\//', $httpUri)) {
            $httpUri = explode("/", $httpUri);
            unset($httpUri[1]);
            $httpUri = implode("/", $httpUri);
        }

        $request = new Request($router, $httpMethod, $httpUri);
        $request->payload(self::Payload($request->_method));
        self::Headers($request);

        $controller = $request->send();
        unset($request);

        // Accept Header
        $acceptHeader = $_SERVER["HTTP_ACCEPT"] ?? null;
        if ($acceptHeader) {
            $acceptHeader = explode(";", $acceptHeader);
            $acceptHeader = explode(",", $acceptHeader[0]);
            foreach ($acceptHeader as $format) {
                try {
                    $controller->response()->format(trim($format));
                } catch (ControllerResponseException $e) {
                    continue; // Ignore
                }
            }
        }

        // Callback
        if ($closure) {
            call_user_func($closure, $controller);
        }

        return $controller;
    }

    /**
     * @param Request $request
     */
    protected static function Headers(Request $request): void
    {
        foreach ($_SERVER as $key => $value) {
            if (substr($key, 0, 5) === "HTTP_") {
                $request->headers()->set(substr($key, 5), $value);
            }
        }
    }

    /**
     * @param string $method
     * @return array
     * @throws RESTfulException
     */
    protected static function Payload(string $method): array
    {
        $payload = []; // Initiate payload
        $contentType = trim(explode(";", $_SERVER["CONTENT_TYPE"] ?? "")[0]);

        // Ready query string
        if (isset($_SERVER["QUERY_STRING"])) {
            parse_str($_SERVER["QUERY_STRING"], $payload);
        }

        // Get input body from stream
        $payloadMerge = null;
        $payloadBody = null;
        switch ($method) {
            case "POST":
                $payloadMerge = $_POST;
                if ($contentType === "application/json") {
                    $payloadBody = file_get_contents("php://input");
                }
                break;
            case "PUT":
            case "DELETE":
                $payloadBody = file_get_contents("php://input");
                break;
        }

        if ($payloadBody) {
            switch ($contentType) {
                case "application/json":
                    $payloadMerge = json_decode($payloadBody, true);
                    break;
                case "application/x-www-form-urlencoded":
                    $payloadMerge = [];
                    parse_str($payloadBody, $payloadMerge);
                    break;
                case "multipart/form-data":
                    if ($method !== "POST") {
                        throw RESTfulException::payloadMethodTypeError($method, $contentType);
                    }
                    break;
                default:
                    $payloadMerge = [];
            }

            if (!is_array($payloadMerge)) {
                throw RESTfulException::payloadStreamError($method, $contentType);
            }

            unset($payloadBody);
        }

        if (is_array($payloadMerge)) {
            $payload = array_merge($payload, $payloadMerge);
        }

        return $payload;
    }
}