<?php
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
}