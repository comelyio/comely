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

use Comely\IO\HttpRouter\Controller\Request;
use Comely\IO\HttpRouter\Controller\Response;

/**
 * Class Controller
 * @package Comely\IO\HttpRouter
 */
abstract class Controller
{
    /** @var Router */
    private $router;
    /** @var Request */
    private $request;
    /** @var Response */
    private $response;

    /**
     * Controller constructor.
     * @param Router $router
     * @param Request $request
     */
    final public function __construct(Router $router, Request $request)
    {
        $this->router = $router;
        $this->request = $request;
        $this->response = new Response();
    }

    /**
     * @return Router
     */
    final protected function router(): Router
    {
        return $this->router;
    }

    /**
     * @return Request
     */
    final public function request(): Request
    {
        return $this->request;
    }

    /**
     * @return Response
     */
    final public function response(): Response
    {
        return $this->response;
    }
}