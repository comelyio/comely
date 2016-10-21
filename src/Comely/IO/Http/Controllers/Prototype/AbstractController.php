<?php
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
        $controller =   $req->getController();
        $input  =   $req->getInput();

        // Work with request and controller, populate Response

        $res->send();
    }
}