<?php
/**
 * This file is part of Comely package.
 * https://github.com/comelyio/comely
 *
 * Copyright (c) 2016-2019 Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/comelyio/comely/blob/master/LICENSE
 */

declare(strict_types=1);

namespace Comely\IO\FileSystem\Disk;

use Comely\IO\FileSystem\Disk\AbstractPath\Functions;
use Comely\IO\FileSystem\Exception\PathException;

/**
 * Class AbstractPath
 * @package Comely\IO\FileSystem\Disk
 */
abstract class AbstractPath implements DiskInterface
{
    /** @var string */
    private $path;
    /** @var null|string */
    private $parent;
    /** @var Functions */
    private $functions;
    /** @var null|Privileges */
    private $privileges;

    /**
     * AbstractPath constructor.
     * @param string $path
     * @param Directory|null $parent
     * @param bool $clearCache
     * @throws PathException
     */
    protected function __construct(string $path, ?Directory $parent = null, bool $clearCache = true)
    {
        if ($clearCache) {
            clearstatcache(true);
        }

        $this->path = Paths::Absolute($path, $parent);
        $this->parent = $parent;
        $this->functions = Functions::getInstance();

        // Making sure we are working in local environment
        if (!stream_is_local($this->path)) {
            throw new PathException('Path to file/directory must be on local machine');
        }
    }

    /**
     * @return int
     */
    abstract public function is(): int;

    /**
     * @return Functions
     */
    final protected function functions(): Functions
    {
        return $this->functions;
    }

    /**
     * To save system resources, privileges will only be checked (once) if this method is called
     * @param bool $recheck
     * @return Privileges
     */
    final public function permissions(bool $recheck = false): Privileges
    {
        if (!$this->privileges || $recheck) {
            $this->privileges = new Privileges($this->path);
        }

        return $this->privileges;
    }

    /**
     * @return string
     */
    final public function path(): string
    {
        return $this->path;
    }

    /**
     * @return Directory|null
     */
    final public function parent(): ?Directory
    {
        return $this->parent ?? null;
    }
}