<?php
declare(strict_types=1);

namespace Comely\IO\DependencyInjection;

use Comely\IO\DependencyInjection\Exception\ContainerException;
use Comely\IO\DependencyInjection\Container\Service;

/**
 * Class Container
 * @package Comely\IO\DependencyInjection
 */
class Container extends AbstractDI
{
    /** @var array */
    private $services;
    /** @var Repository */
    private $repo;

    /**
     * Container constructor.
     */
    public function __construct()
    {
        $this->services =   [];
        $this->repo =   new Repository();
    }

    /**
     * Checks if container has a service for $key
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key) : bool
    {
        return (array_key_exists($key, $this->services)  ||  $this->repo->has($key)) ? true : false;
    }

    /**
     * Add a new instance or service in container
     *
     * @param string $key
     * @param $obj
     * @param callable|null $callback
     * @throws ContainerException
     * @throws Exception\RepositoryException
     */
    public function add(string $key, $obj, callable $callback = null)
    {
        // Check if key already exists
        if($this->has($key)) {
            throw ContainerException::keyExists(__METHOD__, $key);
        }

        // Determine service type
        if(is_object($obj)) {
            // Saving instance, so same instance will be returned every time
            $this->repo->push($obj, $key);
        } elseif(is_string($obj)) {
            // Save path to class, new instance will be created and returned on retrieve
            $this->services[$key]   =   new Service($obj);
            // Callback with reference to service
            if(isset($callback)) {
                call_user_func_array($callback, [$this]);
            }
        } else {
            // Bad service
            throw ContainerException::badService(__METHOD__, gettype($obj));
        }
    }

    /**
     * Retrieve an instance of service from container
     *
     * @param string $key
     * @param array ...$args
     * @return mixed
     * @throws ContainerException
     * @throws Exception\RepositoryException
     */
    public function get(string $key, ...$args)
    {
        // Check if service exists
        if(!$this->has($key)) {
            throw ContainerException::serviceNotFound(__METHOD__, $key);
        }

        // Check if its in Repository
        if($this->repo->has($key)) {
            // Pull instance from repository
            return $this->repo->pull($key);
        } else {
            // Get new instance of service class
            $service    =   $this->services[$key];
            return $service->createInstance($this, $args);
        }
    }
}