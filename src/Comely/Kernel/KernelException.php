<?php
declare(strict_types=1);

namespace Comely\Kernel;

/**
 * Class KernelException
 * @package Comely\Kernel
 */
class KernelException extends \ComelyException
{
    protected static $componentId   =   __NAMESPACE__;
}