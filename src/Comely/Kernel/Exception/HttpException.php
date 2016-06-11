<?php
declare(strict_types=1);

namespace Comely\Kernel\Exception;

use Comely\Kernel\KernelException;

/**
 * Class HttpException
 * @package Comely\Kernel\Exception
 */
class HttpException extends KernelException
{
    protected static $componentId   =   "Comely\\Kernel\\Http";
}