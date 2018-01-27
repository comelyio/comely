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

namespace Comely\IO\Http\Controllers\Prototype;

use Comely\IO\Http\Controllers\ControllerInterface;
use Comely\IO\Http\Request;
use Comely\IO\Http\Request\Response;

/**
 * Class AbstractController
 * @package Comely\IO\Http\Controllers
 */
abstract class AbstractController implements ControllerInterface
{
    /**
     * @param Request $req
     * @param Response $res
     */
    public function init(Request $req, Response $res)
    {
        /** @noinspection PhpUnusedLocalVariableInspection */
        $controller =   $req->getController();
        /** @noinspection PhpUnusedLocalVariableInspection */
        $input  =   $req->getInput();

        // Work with request and controller, populate Response

        $res->send();
    }
}