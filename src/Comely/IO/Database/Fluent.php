<?php
declare(strict_types=1);

namespace Comely\IO\Database;

use Comely;
use Comely\IO\Database\Exception\FluentException;
use Comely\IO\Database\Exception\SchemaException;
use Comely\IO\Database\Schema\AbstractTable;

/**
 * Class Fluent ORM
 * @package Comely\IO\Database
 */
abstract class Fluent
{
    private $modelName;
    private $schemaTable;
    private $row;

    /**
     * Fluent constructor.
     *
     * When directly constructing a model that extends Fluent, no argument should be passed as these parameters accept
     * NULL values and they are only intended to be filled by AbstractTable::findBy[COL*] method
     *
     * @param string|null $table
     * @param array|null $row
     * @throws FluentException
     */
    final public function __construct(string $table = null, array $row = null)
    {
        // Set modelName
        $this->modelName    =   get_called_class();

        // Check if table relation is defined
        if(!defined("static::SCHEMA_TABLE")) {
            throw FluentException::initConstant("SCHEMA_TABLE", $this->modelName);
        }

        // Check if $table is provided for cross-checking
        if(!is_null($table)) {
            // Cross-check $table with model's SCHEMA_TABLE
            if($table   !== static::SCHEMA_TABLE) {
                // On fail, Cross-check if $table has SCHEMA_TABLE constant and that matches
                $tableConstant  =   sprintf("%s::SCHEMA_TABLE", $table);
                if(!defined($tableConstant) ||  constant($tableConstant)    !==    static::SCHEMA_TABLE) {
                    // Model and table are NOT related
                    throw FluentException::tableModelMismatch($this->modelName, static::SCHEMA_TABLE, $table);
                }
            }
        }

        // Save AbstractTable instance
        $this->schemaTable    =   Schema::table(static::SCHEMA_TABLE);

        // Bootstrap data mapping
        $this->row  =   [];

        // Check if $row is Array
        if(is_array($row)) {
            // Verify that $row has all columns defined for AbstractTable
            $columnsKeys    =   $this->schemaTable->getColumnsKeys();
            foreach($columnsKeys as $column) {
                if(!array_key_exists($column, $row)) {
                    throw FluentException::missingColumn($column, $this->modelName);
                }
            }

            // TODO: Exclude keys

            // Data mapping
            foreach($row as $key => $value) {
                // TODO: Exclude keys

                // TODO: Type cast according to data types

                $this->row[$key]    =   $value;
            }


        }

        // Check if callBack method is defined
        // callBack() method is used for customizing mapping done by Fluent or perform other actions
        if(method_exists($this, "callBack")) {
            call_user_func([$this,"callBack"]);
        }
    }

    /**
     * Sets value of a column
     * 
     * @param string $name
     * @param $value
     * @throws FluentException
     * @throws SchemaException
     */
    final public function __set(string $name, $value)
    {
        // Convert $name to snake_case
        $snakeName  =   Comely::snakeCase($name);

        // Get column
        $column =   $this->schemaTable->getColumn($snakeName);

        // Cross check value type with column's
        if($column->scalarType  !== gettype($value)) {
            // Check if value type is NULL and column is nullable
            if(gettype($value)  === "NULL"  &&  array_key_exists("nullable", $column->attributes)) {
                // Column is NULLable
            } else {
                // Data type of value doesn't match with column's
                throw FluentException::badColumnValue($this->modelName, $name, $column->scalarType, gettype($value));
            }
        }

        // Set value
        $this->row[$snakeName]   =   $value;
    }

    /**
     * Gets column value or NULL
     *
     * @param $name
     * @return mixed
     */
    final public function __get($name)
    {
        // Convert $col name to snake_case
        $snakeName  =   Comely::snakeCase($name);
        return $this->row[$snakeName] ?? null;
    }

    /**
     * Save Changes (INSERT ... ON DUPLICATE KEY UPDATE)
     *
     * This method will UPDATE an existing row in database that matches UNIQUE KEY constraint(s) or insert a new row.
     * Callback function can be passed as argument which will be called back on SUCCESS (no error received from
     * database server) with return value (bool) passed as first argument and reference to database instance as
     * second argument.
     *
     * This method will not check if any UNIQUE KEYs were defined. If a table does not have any UNIQUE KEY defined,
     * this method will insert a new row each time. If no rows were affected/inserted but query was successful, this
     * method will throw exception.
     * 
     * @param callable|null $callback
     * @return bool
     * @throws FluentException
     */
    final public function save(callable $callback = null) : bool
    {
        // Iterate through each column in row and prepare query
        $values =   [];
        foreach($this->row as $key => $value) {
            // Statement pieces
            $insertKeys[]   =   "`". $key ."`";
            $insertValues[] =   ":i_" . $key;
            $updateKeys[]   =   sprintf('`%s`=:u_%s', $key, $key);

            // Values
            $values["i_" . $key]    =   $value;
            $values["u_" . $key]    =   $value;
        }

        // Prepare pieces
        $insertKeys =   implode(", ", $insertKeys);
        $insertValues =   implode(", ", $insertValues);
        $updateKeys =   implode(", ", $updateKeys);

        // Prepare query
        $schemaTable    =   $this->schemaTable;
        $schemaDb   =   $schemaTable->getDb();
        $query  =   sprintf(
            'INSERT INTO `%1$s` (%2$s) VALUES (%3$s) ON DUPLICATE KEY UPDATE %4$s',
            $schemaTable::SCHEMA_TABLE,
            $insertKeys,
            $insertValues,
            $updateKeys
        );

        // Execute query
        $result   =   $schemaDb->query($query, $values, Database::QUERY_EXEC);
        if(!$result) {
            throw FluentException::arQueryError(__METHOD__, $schemaDb->lastQuery->error ?? "Failed");
        }

        // Table was affected?
        if($schemaDb->lastQuery->rows   !== 1) {
            throw FluentException::arQueryError(__METHOD__, 'Row wasn\'t inserted or updated');
        }

        // If callback function is callable
        if(is_callable($callback)) {
            $callback($result, $schemaDb);
        }

        // Return $result
        return $result;
    }

    /**
     * Insert Row
     *
     * INSERT row in table and return lastInsertId(AbstractTable::primaryKey)
     *
     * Callback function can be passed as argument which will be called back on SUCCESS (no error received from
     * database) with return value (int:lastInsertId) passed as first argument and reference to database instance as
     * second argument.
     *
     * @param callable|null $callback
     * @return int
     * @throws FluentException
     */
    final public function insert(callable $callback = null) : int
    {
        // Iterate through each column in row and prepare query
        $values =   [];
        foreach($this->row as $key => $value) {
            // Statement pieces
            $insertKeys[]   =   "`". $key ."`";
            $insertValues[] =   ":" . $key;
            $values[$key]    =   $value;
        }

        // Prepare pieces
        $insertKeys =   implode(", ", $insertKeys);
        $insertValues =   implode(", ", $insertValues);

        // Prepare query
        $schemaTable    =   $this->schemaTable;
        $schemaDb   =   $schemaTable->getDb();
        $query  =   sprintf(
            'INSERT INTO `%1$s` (%2$s) VALUES (%3$s)',
            $schemaTable::SCHEMA_TABLE,
            $insertKeys,
            $insertValues
        );

        // Execute query
        $insert   =   $schemaDb->query($query, $values, Database::QUERY_EXEC);
        $insertId   =   $schemaDb->lastInsertId($schemaTable->getPrimaryKey());
        if(!$insert) {
            throw FluentException::arQueryError(__METHOD__, $schemaDb->lastQuery->error ?? "Failed");
        }

        // lastInsertId?
        if(!$insertId) {
            throw FluentException::arQueryError(__METHOD__, "Failed to retrieve lastInsertId()");
        }

        // If callback function is callable
        if(is_callable($callback)) {
            $callback($insertId, $schemaDb);
        }

        // Return $insertId
        return $insertId;
    }

    /**
     * Update Row
     *
     * Row in table is identified with primaryKey set for AbstractTable. If table hasn't defined primaryKey, or
     * primaryKey value is not set in model then an exception will be thrown. If query was successful but no rows were
     * affected then exception will be thrown as well.
     *
     * Callback function can be passed as argument which will be called back on SUCCESS (no error received from
     * database server) with return value (bool) passed as first argument and reference to database instance as
     * second argument.
     *
     * @param callable|null $callback
     * @return bool
     * @throws FluentException
     */
    final public function update(callable $callback = null) : bool
    {
        $schemaTable    =   $this->schemaTable;
        $schemaDb   =   $schemaTable->getDb();

        // Check if primaryKey is set
        $primaryKey =   $schemaTable->getPrimaryKey();
        if(empty($primaryKey)) {
            // primaryKey is not set in table
            throw FluentException::arQueryError(__METHOD__, "Primary key must be defined first");
        } elseif(!array_key_exists($primaryKey, $this->row)) {
            // primaryKey is not set in model
            throw FluentException::arQueryError(__METHOD__, sprintf(
                'Value of primary key "%1$s" must be set',
                $primaryKey
            ));
        }

        // Iterate through each column in row and prepare query
        $values =   [];
        foreach($this->row as $key => $value) {
            // Statement pieces
            $updateKeys[]   =   sprintf('`%s`=:%s', $key, $key);
            $values[$key]   =   $value;

            if($key === $primaryKey) {
                $values["w_" . $key]    =   $value;
            }
        }

        // Prepare query
        $updateKeys =   implode(", ", $updateKeys);
        $query  =   sprintf(
            'UPDATE `%1$s` SET %2$s WHERE `%3$s`=:w_%3$s',
            $schemaTable::SCHEMA_TABLE,
            $updateKeys,
            $primaryKey
        );

        // Execute query
        $result   =   $schemaDb->query($query, $values, Database::QUERY_EXEC);
        if(!$result) {
            throw FluentException::arQueryError(__METHOD__, $schemaDb->lastQuery->error ?? "Failed");
        }

        // Table was affected?
        if($schemaDb->lastQuery->rows   !== 1) {
            throw FluentException::arQueryError(__METHOD__, sprintf(
                'Failed to update row with primary key "%1$s"',
                $primaryKey
            ));
        }

        // If callback function is callable
        if(is_callable($callback)) {
            $callback($result, $schemaDb);
        }

        // Return $result
        return $result;
    }



    /**
     * Delete Row
     *
     * Row in table is identified with primaryKey set for AbstractTable. If table hasn't defined primaryKey, or
     * primaryKey value is not set in model then an exception will be thrown. If query was successful but no rows were
     * affected then exception will be thrown as well.
     *
     * Callback function can be passed as argument which will be called back on SUCCESS (no error received from
     * database server) with return value (bool) passed as first argument and reference to database instance as
     * second argument.
     *
     * @param callable|null $callback
     * @return bool
     * @throws FluentException
     */
    final public function delete(callable $callback = null) : bool
    {
        $schemaTable    =   $this->schemaTable;
        $schemaDb   =   $schemaTable->getDb();

        // Check if primaryKey is set
        $primaryKey =   $schemaTable->getPrimaryKey();
        if(empty($primaryKey)) {
            // primaryKey is not set in table
            throw FluentException::arQueryError(__METHOD__, "Primary key must be defined first");
        } elseif(!array_key_exists($primaryKey, $this->row)) {
            // primaryKey is not set in model
            throw FluentException::arQueryError(__METHOD__, sprintf(
                'Value of primary key "%1$s" must be set',
                $primaryKey
            ));
        }

        // Prepare query
        $query  =   sprintf(
            'DELETE FROM `%1$s` WHERE `%2$s`=?',
            $schemaTable::SCHEMA_TABLE,
            $primaryKey
        );

        // Execute query
        $result   =   $schemaDb->query($query, [$this->row[$primaryKey]], Database::QUERY_EXEC);
        if(!$result) {
            throw FluentException::arQueryError(__METHOD__, $schemaDb->lastQuery->error ?? "Failed");
        }

        // Table was affected?
        if($schemaDb->lastQuery->rows   !== 1) {
            throw FluentException::arQueryError(__METHOD__, sprintf(
                'Failed to delete row with primary key "%1$s"',
                $primaryKey
            ));
        }

        // If callback function is callable
        if(is_callable($callback)) {
            $callback($result, $schemaDb);
        }

        // Return $result
        return $result;
    }
}