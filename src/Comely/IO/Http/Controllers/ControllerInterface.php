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

namespace Comely\IO\Http\Controllers;

use Comely\IO\Http\Request;
use Comely\IO\Http\Request\Response;

/**
 * Interface ControllerInterface
 * @package Comely\IO\Http\Controllers
 */
interface ControllerInterface
{
    /**
     * This method is called with reference to Request instance, after controller has been constructed.
     *
     * Use this method to further resolve request and call necessary method with in controller to populate
     * Response instance and send output to browser.
     *
     * @param Request $request
     * @param Response $response
     */
    public function init(Request $request, Response $response);

    /**
     * This method is called from "init" method as the request finishes
     * @return mixed
     */
    public function finish();
}