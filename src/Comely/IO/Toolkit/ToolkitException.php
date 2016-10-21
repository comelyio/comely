<?php
declare(strict_types=1);

namespace Comely\IO\Toolkit;

/**
 * Class ToolkitException
 * @package Comely\IO\Toolkit
 */
class ToolkitException extends \ComelyException
{
    /** @var string */
    protected static $componentId   =   __NAMESPACE__;
}