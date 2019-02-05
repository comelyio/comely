<?php
/**
 * This file is part of Comely package.
 * https://github.com/comelyio/comely
 *
 * Copyright (c) 2016-2019 Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/comelyio/comely/blob/master/LICENSE
 */

declare(strict_types=1);

namespace Comely\IO\HttpRouter\Router;

use Comely\IO\HttpRouter\Controller\Data\Payload;
use Comely\IO\HttpRouter\Exception\RoutingException;

/**
 * Class Response
 * @package Comely\IO\HttpRouter\Router
 */
class Response
{
    /** @var array */
    private $handlers;
    /** @var \Closure */
    private $defaultHandler;

    /**
     * Response constructor.
     */
    public function __construct()
    {
        $this->handlers = [];
        $this->defaultHandler = function (Payload $payload) {
            $body = $payload->get("body");
            if ($body) { // Has body?
                return print $body;
            }

            return print_r($payload->array());
        };

        // Default response handles
        $this->handle("application/json", function (Payload $payload) {
            header('Content-type: application/json; charset=utf-8');
            print json_encode($payload->array());
        });
    }

    /**
     * @param string $format
     * @return bool
     */
    public function isValid(string $format): bool
    {
        return preg_match('/^[a-z]+\/[a-z]+$/', $format) ? true : false;
    }

    /**
     * @param string $type
     * @param \Closure $callback
     * @return Response
     * @throws RoutingException
     */
    public function handle(string $type, \Closure $callback): self
    {
        if (!$this->isValid($type)) {
            throw new RoutingException('Invalid response/content type');
        }

        $this->handlers[$type] = $callback;
        return $this;
    }

    /**
     * @param string $type
     * @param Payload $payload
     * @throws RoutingException
     */
    public function send(string $type, Payload $payload)
    {
        if (!$this->isValid($type)) {
            throw new RoutingException('Invalid response/content type');
        }

        /** @var \Closure $closure */
        $closure = $this->handlers[$type] ?? $this->defaultHandler;
        call_user_func($closure, $payload);
    }
}