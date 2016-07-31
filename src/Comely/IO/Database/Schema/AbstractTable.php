<?php
declare(strict_types=1);

namespace Comely\IO\Database\Schema;

use Comely;
use Comely\IO\Database\Database;
use Comely\IO\Database\Exception\SchemaException;
use Comely\IO\Database\Schema;
use Comely\IO\Database\Schema\Table\Column;
use Comely\IO\Database\Schema\Table\Constants;
use Comely\IO\Database\Schema\Table\TableBuilder;

/**
 * Class AbstractTable
 * @package Comely\IO\Database\Schema
 */
abstract class AbstractTable implements Constants
{
    private $db;
    private $dbDriver;
    private $columns;
    private $columnsKeys;
    private $constraints;
    private $tableId;
    private $tableName;
    private $tableEngine;
    private $primaryKey;

    /**
     * AbstractTable constructor.
     * @param Database $db
     * @throws SchemaException
     */
    final public function __construct(Database $db)
    {
        // Bootstrap
        $this->db   =   $db;
        $this->dbDriver =   $this->db->driver();
        $this->columns  =   [];
        $this->columnsKeys  =   [];
        $this->constraints  =   [];
        $this->primaryKey   =   null;

        // Check if necessary constants are defined
        if(!defined("static::SCHEMA_TABLE")) {
            throw SchemaException::tableInitConstant("SCHEMA_TABLE");
        } elseif(!defined("static::SCHEMA_MODEL")) {
            throw SchemaException::tableInitConstant("SCHEMA_MODEL");
        }

        // Set table's name
        $this->tableId  =   get_called_class();
        $this->tableName    =   static::SCHEMA_TABLE;
        $this->tableEngine  =   "InnoDB";

        // Check if SCHEMA_MODEL is not NULL and it is an existing class
        if(!is_null(static::SCHEMA_MODEL)) {
            $modelClass =   strval(static::SCHEMA_MODEL);
            if(!class_exists($modelClass)) {
                throw SchemaException::badModel($this->tableId, $modelClass);
            }
        }

        // Check if createTable method exists
        if(method_exists($this, "createTable")) {
            // Call createTable method
            call_user_func([$this, "createTable"]);
        }

        // Save reference with table name in Schema
        // Schema::getTable() method can be called with table names instead of class names however this is not
        // recommended as it can cause conflicts in project using multiple databases
        Schema::createTable($this->tableName, $this);
    }

    /**
     * Use TableBuilder to make "CREATE TABLE" sql query
     *
     * @param bool $dropExisting
     * @return string
     * @throws SchemaException
     */
    final public function tableBuilder(bool $dropExisting = false) : string
    {
        // Get Builder instance
        $builder    =   TableBuilder::getInstance();

        // Set schemaTable and return buildQuery
        return $builder->setTable($this)->buildQuery();
    }

    /**
     * Get all column names
     * 
     * @return array
     */
    final public function getColumnsKeys() : array
    {
        return $this->columnsKeys;
    }

    /**
     * Get a column
     * 
     * @param string $name
     * @return Column
     * @throws SchemaException
     */
    final public function getColumn(string $name) : Column
    {
        // Check if column exists
        if(array_key_exists($name, $this->columns)) {
            return $this->columns[$name];
        }

        // Column not found
        throw SchemaException::undefinedColumn($name, $this->tableId);
    }

    /**
     * Get all columns
     *
     * @return array
     */
    public function getColumns() : array
    {
        return $this->columns;
    }

    /**
     * Get all defined constraints
     *
     * @return array
     */
    final public function getConstraints() : array
    {
        return $this->constraints;
    }

    /**
     * Get reference to Database object of AbstractTable
     *
     * @return Database
     */
    final public function getDb() : Database
    {
        return $this->db;
    }

    /**
     * Gets PRIMARY KEY
     *
     * @return string|null
     */
    final public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    /**
     * @return string
     */
    final public function getName() : string
    {
        return $this->tableName;
    }

    /**
     * Using OOP magic to create magical findBy* methods
     * Arguments:
     * First = (mixed) Value to search in database with
     * Second = (int) Number of rows to return
     * Third = (bool) Throw exception on no rows found or return false?
     *
     * @param $name
     * @param $arguments
     * @throws SchemaException
     * @return object
     */
    final public static function __callStatic($name, $arguments)
    {
        // Check if calling findBy* method
        if(substr($name, 0, 6)  === "findBy") {
            $findBy =   substr($name, 6);
            $findValue[]  =   $arguments[0] ?? null;
            $findLimit  =   1;
            if(array_key_exists(1, $arguments)) {
                // Check if fetch limit is explicitly provided
                if(is_int($arguments[1])) {
                    // Fetch limit is provided and is an Integer
                    $findLimit  =   $arguments[1];
                } else {
                    // Fetch limit must be an Integer
                    throw SchemaException::badArgType(__METHOD__, 2, "integer", gettype($arguments[1]));
                }
            }

            // Throw exception is row not found?
            $throwException =   true;
            if(isset($arguments[2]) &&  $arguments[2]   === false) {
                $throwException =   false;
            }

            // Convert PascalCase/camelCase to snake_case
            $findBy =   Comely::snakeCase($findBy);

            // SELECT query
            $findQuery  =   sprintf(
                'SELECT * FROM `%1$s` WHERE `%2$s`=? LIMIT %3$s',
                static::SCHEMA_TABLE,
                $findBy,
                $findLimit
            );

            // Run Query
            $tableName =   get_called_class();
            $db  =   Schema::getTable($tableName)->getDb();
            $rows    =   $db->query($findQuery, $findValue, Database::QUERY_FETCH);
            // Row(s) were found?
            if(empty($rows)) {
                // No rows were found
                if($throwException) {
                    throw new SchemaException(
                        $name,
                        "No rows were returned from database",
                        1201
                    );
                } else {
                    // Return FALSE
                    return false;
                }
            }

            // Check if SCHEMA_MODEL constant is set
            $modelClass  =   static::SCHEMA_MODEL;
            $callbackArgs  =   Schema::getCallbackArgs();
            if(!is_null($modelClass)) {
                if($findLimit   === 1) {
                    // Return single model instance
                    $model  =   new $modelClass($tableName, $rows[0]);
                    // Arguments injection
                    if(method_exists($model, "callBack")) {
                        $model->callBack(...$callbackArgs);
                    }
                    
                    return $model;
                } else {
                    $models =   [];
                    // Iterate through rows
                    foreach($rows as $row) {
                        $model   =   new $modelClass($tableName, $row);
                        // Arguments injection
                        if(method_exists($model, "callBack")) {
                            $model->callBack(...$callbackArgs);
                        }

                        $models[]   =   $model;
                    }

                    // return indexed Array containing Model's instances
                    return $models;
                }
            } else {
                // Model is not set, return fetched Array
                return $findLimit   === 1 ? $rows[0] : $rows;
            }
        }

        // Throw undefined method exception
        throw SchemaException::undefinedMethod($name);
    }

    /**
     * @param $name
     * @param $arguments
     * @return object
     * @throws SchemaException
     */
    final public function __call($name, $arguments)
    {
        return self::__callStatic($name, $arguments);
    }
    
    /**
     * Set storage engine (for MySQL)
     *
     * @param string $engine
     * @return AbstractTable
     */
    final public function setEngine(string $engine) : self
    {
        $this->tableEngine  =   $engine;
        return $this;
    }

    /**
     * Get table engine
     *
     * @return string
     */
    final public function getEngine() : string
    {
        return $this->tableEngine;
    }

    /**
     * Defines an Integer column
     *
     * @param string $name
     * @param int $size
     * @param int|null $digits
     * @return Column
     * @throws SchemaException
     */
    final protected function int(string $name, int $size = self::INT_DEFAULT, int $digits = null) : Column
    {
        // Check column's name
        Column::checkName($name);

        // Check Integer size
        if(!in_array($size, [1,2,4,8,16])) {
            // size param. must be passed with one of AbstractTable::INT_* flags
            throw SchemaException::badIntegerSize();
        }

        // Create column
        $this->columnsKeys[]    =   $name;
        $this->columns[$name]    =   new Column;
        $this->columns[$name]->type =   "int";
        $this->columns[$name]->scalarType =   "integer";
        $this->columns[$name]->flag   =   $size;

        // Integer has specified number of digits?
        if(is_int($digits)) {
            $this->columns[$name]->attributes["digits"] =   $digits;
        }

        // Return Column object for further attribution
        return  $this->columns[$name];
    }

    /**
     * Defines a String (char|varchar) column
     *
     * @param string $name
     * @param int $len
     * @param int $flag
     * @return Column
     * @throws SchemaException
     */
    final protected function string(string $name, int $len = 255, int $flag = self::STR_VARIABLE) : Column
    {
        // Check column's name
        Column::checkName($name);

        // Check variability flag
        if(!in_array($flag, [self::STR_FIXED, self::STR_VARIABLE])) {
            // String size must be declared Fixed (char) or Variable (varchar)
            throw SchemaException::badFlag($name, "str");
        }

        // Create String column
        $this->columnsKeys[]    =   $name;
        $this->columns[$name]   =   new Column;
        $this->columns[$name]->type =   "string";
        $this->columns[$name]->scalarType =   "string";
        $this->columns[$name]->flag =   $flag;
        $this->columns[$name]->attributes["length"] =   $len;

        // Return Column object for further attribution
        return  $this->columns[$name];
    }

    /**
     * Defines a TEXT column
     *
     * @param string $name
     * @param int $flag 
     * @return Column
     * @throws SchemaException
     */
    final protected function text(string $name, int $flag = self::TEXT_DEFAULT) : Column
    {
        // Check column's name
        Column::checkName($name);

        // Check variability flag
        if(!in_array($flag, [self::TEXT_DEFAULT, self::TEXT_MEDIUM, self::TEXT_LONG])) {
            throw SchemaException::badFlag($name, "text");
        }

        // Create Text column
        $this->columnsKeys[]    =   $name;
        $this->columns[$name]   =   new Column;
        $this->columns[$name]->type =   "text";
        $this->columns[$name]->scalarType =   "string";
        $this->columns[$name]->flag =   $flag;

        // Return Column object for further attribution
        return  $this->columns[$name];
    }

    /**
     * Defines an ENUM column
     *
     * @param string $name
     * @param \string[] ...$opts
     * @return Column
     */
    final protected function enum(string $name, string ...$opts) : Column
    {
        // Check column's name
        Column::checkName($name);

        // Create Enumeration column
        $this->columnsKeys[]    =   $name;
        $this->columns[$name]   =   new Column;
        $this->columns[$name]->type =   "enum";
        $this->columns[$name]->scalarType =   "string";
        $this->columns[$name]->attributes["options"] =   $opts;

        // Return Column object for further attribution
        return  $this->columns[$name];
    }

    /**
     * Defines a ("double"-precision) Numeric column
     *
     * This column type is appropriate for real|float|double numeric types.
     * Parameters $m and $d don't have default values since MySQL determines limits permitted by hardware
     *
     * @param string $name
     * @param int $m
     * @param int $d
     * @return Column
     */
    final protected function double(string $name, int $m, int $d) : Column
    {
        // Check column's name
        Column::checkName($name);

        // Create double-precision floating-point numeric column
        $this->columnsKeys[]    =   $name;
        $this->columns[$name]   =   new Column;
        $this->columns[$name]->type =   "double";
        $this->columns[$name]->scalarType =   "double";
        $this->columns[$name]->attributes["m"] =   $m;
        $this->columns[$name]->attributes["d"] =   $d;

        // Return Column object for further attribution
        return  $this->columns[$name];
    }

    /**
     * Defines a Decimal column
     *
     * @param string $name
     * @param int $m
     * @param int $d
     * @return Column
     */
    final protected function decimal(string $name, int $m = 10, $d = 0) : Column
    {
        // Check column's name
        Column::checkName($name);

        // Create Decimal column
        $this->columnsKeys[]    =   $name;
        $this->columns[$name]   =   new Column;
        $this->columns[$name]->type =   "decimal";
        $this->columns[$name]->scalarType =   "double";
        $this->columns[$name]->attributes["m"] =   $m;
        $this->columns[$name]->attributes["d"] =   $d;

        // Return Column object for further attribution
        return  $this->columns[$name];
    }

    /**
     * Creates a UNIQUE KEY constraint
     *
     * Supported database drivers: MySQL, SQLite
     *
     * @param string $name
     * @param \string[] ...$cols
     * @throws SchemaException
     */
    final protected function uniqueKey(string $name, string ...$cols)
    {
        // Check database driver
        if(!in_array($this->dbDriver, ["mysql","sqlite"], true)) {
            throw SchemaException::unSupportedColumn($name, "Unique Constraint", $this->dbDriver);
        }

        // Save constraint
        $this->constraints[$name]   =   ["type" => "unique", "cols" => $cols];
    }

    /**
     * Creates a FOREIGN KEY constraint
     *
     * Supported database drivers: MySQL, SQLite
     *
     * @param string $colName
     * @param string $foreignTable
     * @param string $foreignCol
     * @throws SchemaException
     */
    final protected function foreignKey(string $colName, string $foreignTable, string $foreignCol)
    {
        // Check database driver
        if(!in_array($this->dbDriver, ["mysql","sqlite"], true)) {
            throw SchemaException::unSupportedColumn($colName, "Foreign Constraint", $this->dbDriver);
        }

        // Save constraint
        $this->constraints[$colName]   =   ["type" => "foreign", "table" => $foreignTable, "col" => $foreignCol];
    }

    /**
     * Sets PRIMARY KEY for table
     *
     * @param string $column
     * @throws SchemaException
     */
    final protected function primaryKey(string $column)
    {
        // Check if column exists
        if(!array_key_exists($column, $this->columns)) {
            throw SchemaException::undefinedColumn($column, $this->tableId);
        }
        
        // Set this column as PRIMARY_KEY
        $this->columns[$column]->attributes["primary"]  =   1;
        $this->primaryKey   =   $column;
    }
}