<?php
declare(strict_types=1);

namespace Comely\IO\Filesystem;

/**
 * Class FsException
 * Filesystem Exception
 * @package Comely\IO\Filesystem
 */
class FsException extends \ComelyException
{
    protected static $componentId   =   __NAMESPACE__;
}