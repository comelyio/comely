<?php
/**
 * This file is part of Comely IO package.
 * https://github.com/comelyio/comely
 *
 * Copyright (c) 2016-2017 Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/comelyio/comely/blob/master/LICENSE
 */

declare(strict_types=1);

namespace Comely\IO\DependencyInjection;

use Comely\IO\DependencyInjection\Exception\RepositoryException;

/**
 * Class Repository
 * @package Comely\IO\DependencyInjection
 */
class Repository extends AbstractDI
{
    /** @var array */
    private $instances  =   [];

    /**
     * Save an instance in repository
     *
     * @param $instance
     * @param string|null $key
     * @throws RepositoryException
     */
    public function push($instance, string $key = null)
    {
        // Repository holds only instances
        if(!is_object($instance)) {
            throw RepositoryException::badInstance();
        }

        // Use short/base name of class if $key param is not specified
        $key    =   $key ?? \Comely::baseClassName(get_class($instance));

        // Save instance
        $this->instances[$key]  =   $instance;
    }

    /**
     * Check if repository has instance with $key
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key) : bool
    {
        return array_key_exists($key, $this->instances);
    }

    /**
     * Get an instance from repository
     *
     * @param string $key
     * @param callable|null $callback
     * @return mixed
     * @throws RepositoryException
     */
    public function pull(string $key, callable $callback = null)
    {
        if(!$this->has($key)) {
            if(!is_callable($callback)) {
                throw RepositoryException::instanceNotFound($key);
            }

            return call_user_func_array($callback, [$this,$key]);
        }
        
        return $this->instances[$key];
    }

    /**
     * @return array
     */
    public function list() : array
    {
        $repositories   =   [];
        foreach($this->instances as $key => $value) {
            $repositories[] =   get_class($value);
        }

        return $repositories;
    }
}