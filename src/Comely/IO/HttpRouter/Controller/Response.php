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

namespace Comely\IO\HttpRouter\Controller;

use Comely\IO\HttpRouter\Controller;
use Comely\IO\HttpRouter\Controller\Data\Payload;
use Comely\IO\HttpRouter\Controller\Data\Headers;
use Comely\IO\HttpRouter\Exception\ControllerResponseException;
use Comely\IO\HttpRouter\Exception\RoutingException;

/**
 * Class Response
 * @package Comely\IO\HttpRouter\Controller
 */
class Response
{
    /** @var Controller */
    private $controller;
    /** @var int */
    private $code;
    /** @var string */
    private $format;
    /** @var Payload */
    private $payload;
    /** @var Headers */
    private $headers;

    /**
     * Response constructor.
     * @param Controller $controller
     */
    public function __construct(Controller $controller)
    {
        $this->controller = $controller;
        $this->code = 200;
        $this->payload = new Payload($controller->router());
        $this->headers = new Headers($controller->router());
        $this->format("text/html");
    }

    /**
     * @param string $format
     * @return Response
     * @throws ControllerResponseException
     */
    public function format(?string $format = null): string
    {
        if ($format) {
            if (!$this->controller->router()->response()->isValid($format)) {
                throw new ControllerResponseException('Invalid response/content type');
            }

            $this->format = $format;
        }

        return $this->format;
    }

    /**
     * @param int|null $code
     * @return int
     */
    public function code(?int $code = null): int
    {
        if($code) {
            $this->code = $code;
        }

        return  $this->code;
    }

    /**
     * @return Headers
     */
    public function headers(): Headers
    {
        return $this->headers;
    }

    /**
     * @return Payload
     */
    public function payload(): Payload
    {
        return $this->payload;
    }

    /**
     * @param string $key
     * @param $value
     * @return bool
     */
    public function set(string $key, $value): bool
    {
        return $this->payload->set($key, $value);
    }

    /**
     * @param string $body
     * @return bool
     */
    public function body(string $body): bool
    {
        return $this->payload->set("body", $body);
    }

    /**
     * @throws ControllerResponseException
     */
    public function send(): void
    {
        // Send HTTP response code
        http_response_code($this->code);

        // Send headers
        foreach ($this->headers as $name => $value) {
            header(sprintf('%s: %s', $name, $value));
        }

        // Send Payload/Body
        try {
            $this->controller->router()->response()->send($this->format, $this->payload);
        } catch (RoutingException $e) {
            throw new ControllerResponseException($e->getMessage());
        }
    }
}