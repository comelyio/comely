<?php
declare(strict_types=1);

namespace Comely\IO\DependencyInjection\Container;

use Comely\IO\DependencyInjection\Container;
use Comely\IO\DependencyInjection\Exception\ContainerException;

/**
 * Class Service
 * @package Comely\IO\DependencyInjection\Factory
 */
class Service
{
    private $className;
    private $args;
    private $methods;
    private $properties;

    /**
     * Service constructor.
     * @param string $class
     * @throws ContainerException
     */
    public function __construct(string $class)
    {
        // Check if class exists (reachable by auto-loader)
        if(!class_exists($class, true)) {
            throw ContainerException::classNotFound(__METHOD__, $class);
        }

        $this->className    =   $class;
        $this->args =   [];
        $this->methods  =   [];
        $this->properties   =   [];
    }

    /**
     * Inject a dependency as construct argument
     *
     * @param string $diKey
     */
    public function injectConstructor(string $diKey)
    {
        $this->args[]   =   $diKey;
    }

    /**
     * Inject a dependency via setter method of service's class
     *
     * @param string $method
     * @param string $diKey
     */
    public function injectMethod(string $method, string $diKey)
    {
        $this->methods[$method] =   $diKey;
    }

    /**
     * Inject a dependency to public property of a service's class
     *
     * @param string $property
     * @param string $diKey
     */
    public function injectProperty(string $property, string $diKey)
    {
        $this->properties[$property]    =   $diKey;
    }

    /**
     * Creates new instance of service
     *
     * @param Container $container
     * @param array ...$args
     * @return mixed
     * @throws ContainerException
     */
    public function createInstance(Container $container, ...$args)
    {
        // Prepare arguments for constructor
        $constructorArgs    =   [];
        foreach($this->args as $arg) {
            $constructorArgs[]  =   $container->get($arg);
        }

        // Merge arguments
        $constructorArgs    =   $constructorArgs+$args;

        // Construct
        $class  =   $this->className;
        $instance   =   new $class($constructorArgs);

        // Call setter methods
        foreach($this->methods as $method => $di) {
            if(!is_callable($instance, $method)) {
                // Setter method doesn't exist or isn't publicly callable
                throw ContainerException::injectMethodNotFound(__METHOD__, $class, $method, $di);
            }

            // Call method, inject dependency
            call_user_func_array([$instance,$method], [$container->get($di)]);
        }

        // Inject public properties
        $publicProperties    =   get_object_vars($instance);
        foreach($this->properties as $property => $di)
        {
            // Check if property is public
            if(!in_array($property, $publicProperties)) {
                throw ContainerException::injectPropertyNotFound(__METHOD__, $class, $property, $di);
            }

            // Inject public property
            $instance->$property    =   $container->get($di);
        }

        // Return created instance
        return $instance;
    }
}