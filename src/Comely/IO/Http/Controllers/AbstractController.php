<?php
declare(strict_types=1);

namespace Comely\IO\Http\Controllers;

use Comely\IO\Http\Request;

/**
 * Class AbstractController
 * @package Comely\IO\Http\Controllers
 */
abstract class AbstractController implements ControllerInterface
{
    /**
     * @param Request $request
     */
    public function init(Request $request)
    {
        $controller =   $request->getController();
        $input  =   $request->getInput();
        $response   =   $request->getResponse();

        // Todo: Work with request and controller, populate Response

        $response->send();
    }
}