<?php
declare(strict_types=1);

namespace Comely\IO\Database;

use Comely\IO\Database\Exception\SchemaException;
use Comely\IO\Database\Schema\AbstractTable;

/**
 * Class Schema
 * @package Comely\IO\Database
 */
abstract class Schema
{
    protected static $tables    =   [];

    /**
     * Save an AbstractTable in Schema
     *
     * @param string $name
     * @param AbstractTable $table
     */
    public static function createTable(string $name, AbstractTable $table)
    {
        static::$tables[$name]  =   $table;
    }

    /**
     * Get AbstractTable instance from Schema
     * 
     * @param string $name
     * @return AbstractTable
     * @throws SchemaException
     */
    public static function getTable(string $name) : AbstractTable
    {
        // Check if table is defined in Schema
        if(!array_key_exists($name, static::$tables)) {
           // Not found
            throw SchemaException::tableNotFound($name);
        }

        return static::$tables[$name];
    }

    /**
     * @param string $name
     * @return AbstractTable
     * @throws SchemaException
     * @see getTable()
     */
    public static function table(string $name) : AbstractTable
    {
        return self::getTable($name);
    }

    /**
     * @param Database $db
     * @param string $class
     * @throws SchemaException
     */
    public static function loadTable(Database $db, string $class)
    {
        // Check if $class is path to an existing $class
        // $class must be instanceof AbstractTable
        if(class_exists($class) &&  is_subclass_of($class, "Comely\\IO\\Database\\Schema\\AbstractTable", true)) {
            // Lets instantiate $class
            $table  =   new $class($db);
            
            // Save instance in Schema
            static::$tables[$class] =   $table;
        } else {
            // Not found
            throw SchemaException::tableNotFound($class);
        }
    }
}