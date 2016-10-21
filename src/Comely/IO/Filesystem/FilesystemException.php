<?php
declare(strict_types=1);

namespace Comely\IO\Filesystem;

/**
 * Class FilesystemException
 * @package Comely\IO\Filesystem
 */
class FilesystemException extends \ComelyException
{
    /** @var string */
    protected static $componentId   =   __NAMESPACE__;
}