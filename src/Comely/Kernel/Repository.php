<?php
declare(strict_types=1);

namespace Comely\Kernel;

use Comely\Kernel\Exception\RepositoryException;

/**
 * Class Repository
 * Based on Multiton concept to hold instances
 *
 * IO components and other classes may directly extend this class, and use getInstance() method.
 *
 * Custom user-defined instances can be bound using bind() method and
 * retrieved using find() method.
 *
 * @package Comely\Kernel
 */
class Repository
{
    protected static $instances   =   [];

    /**
     * Get instance of called class
     * This method is for IO components and classes that are directly EXTENDING this class
     */
    public static function getInstance()
    {
        // Name of called class
        $class  =   get_called_class();
        $className  =   substr(strrchr($class, "\\"), 1);

        // Check if object was instantiated
        if(!array_key_exists($className, static::$instances)) {
            // Create a new instance
            static::createKey($className, new static());
        }

        // Return instance
        return self::$instances[$className];
    }

    /**
     * Bind and save an instance in Repository
     *
     * @param string $key
     * @param $instance
     * @return bool
     * @throws RepositoryException
     */
    public static function bind(string $key, $instance) : bool
    {
        // Validate Call, It should be called directly from Repository
        static::validateCall();

        // Bind key and save instance in Repository
        static::createKey($key, $instance);

        return true;
    }

    /**
     * Find an instance in Repository
     *
     * @param string $key
     * @return bool|object
     */
    public static function find(string $key)
    {
        if(array_key_exists($key, static::$instances)) {
            return static::$instances[$key];
        }

        return false;
    }

    /**
     * Fetching a stored Instance magically,
     * This method will throw exception if instance is not found in Repository
     *
     * @param string $name
     * @param array|null $arguments
     * @return bool|object
     * @throws RepositoryException
     */
    public static function __callStatic(string $name, array $arguments = null)
    {
        // Validate Call, It should be called directly from Repository
        static::validateCall();

        // Check is instance is in Repository
        $instance   =   static::find($name);
        if(!$instance) {
            // Instance not found, throw exception
            throw RepositoryException::instanceNotFound($name);
        }

        return $instance;
    }

    /**
     * Bind given Key with one of the stored instances in Repository
     * Keys should not be overridden
     *
     * @param string $key
     * @param $instance
     * @throws RepositoryException
     */
    private static function createKey(string $key, $instance)
    {
        // Validate as word
        if(!preg_match("/^[a-zA-Z]+$/", $key)) {
            throw RepositoryException::badKeyName();
        }

        // Validate Instance
        if(!is_object(($instance))) {
            throw RepositoryException::badInstance();
        }

        // Keys cannot be overridden
        if(array_key_exists($key, static::$instances)) {
            throw RepositoryException::cannotOverrideKey($key);
        }

        // Save key
        static::$instances[$key] =   $instance;
    }

    /**
     * Ensure that certain methods can only be called directly from Repository, not extending classes
     * @throws RepositoryException
     */
    private static function validateCall()
    {
        $called =   get_called_class();
        if($called  !== __CLASS__) {
            throw RepositoryException::badCallReference();
        }
    }

    /**
     * Prevent construction via "new" operator
     */
    private function __construct()
    {
    }

    /**
     * This object should not be cloned
     */
    private function __clone()
    {
    }

    /**
     * Prevent waking up from serialized form
     */
    private function __wakeup()
    {
    }
}