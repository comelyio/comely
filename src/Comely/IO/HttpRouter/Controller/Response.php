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

/**
 * Class Response
 * @package Comely\IO\HttpRouter\Controller
 */
class Response
{
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
        $this->code = 200;
        $this->format($controller->request()->headers()->get("accept") ?? "");
        $this->payload = new Payload($controller->router());
        $this->headers = new Headers($controller->router());
    }

    /**
     * @param int $code
     * @return Response
     */
    public function code(int $code): self
    {
        $this->code = $code;
        return $this;
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
     * @param string $body
     * @return bool
     */
    public function body(string $body): bool
    {
        return $this->payload->set("body", $body);
    }

    /**
     * @param string $accept
     * @return Response
     */
    public function format(string $accept): self
    {
        $accept = explode(";", $accept)[0]; // Grab first part of string
        $accept = explode(",", $accept); // Accept arguments

        $this->format = "text/html";
        foreach ($accept as $format) {
            $format = trim(strtolower($format));
            switch ($format) {
                case "application/json":
                case "application/javascript":
                    $this->format = $format;
                    break;
            }
        }

        return $this;
    }

    public function send(): void
    {
        // Send HTTP response code
        http_response_code($this->code);

        // Send headers
        foreach ($this->headers as $name => $value) {
            header(sprintf('%s: %s', $name, $value));
        }

        // Send Body/Payload
        switch ($this->format) {
            case "application/json":
                header('Content-type: application/json; charset=utf-8');
                print json_encode($this->payload->array());
                return;

        }
    }
}