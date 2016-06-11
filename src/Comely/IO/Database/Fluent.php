<?php
declare(strict_types=1);

namespace Comely\IO\Database;

use Comely\IO\Database\Exception\FluentException;
use Comely\IO\Database\Fluent\Column;

/**
 * Fluent ORM
 * @package Comely\IO\Database
 */
abstract class Fluent
{
    const INT_TINY  =   1;
    const INT_SMALL =   2;
    const INT_MEDIUM    =   4;
    const INT_DEFAULT   =   8;
    const INT_BIG   =   16;
    const STR_FIXED =   32;
    const STR_VARIABLE  =   64;

    protected $db;
    protected $columns;
    protected $tableName;

    /**
     * Fluent constructor.
     * @param Database $db
     * @param string $table
     */
    public function __construct(Database $db, string $table)
    {
        $this->db   =   $db;
        $this->columns  =   [];
        $this->tableName    =   $table;
    }

    /**
     * Defines an Integer column
     *
     * @param string $name
     * @param int $size
     * @param int|null $digits
     * @return Column
     * @throws FluentException
     */
    final protected function int(string $name, int $size = self::INT_MEDIUM, int $digits = null) : Column
    {
        // Check Integer size
        if(!in_array($size, [1,2,4,8,16])) {
            // size param. must be passed with one of Fluent::INT_* flags
            throw FluentException::badIntegerSize();
        }

        // Create column
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
     * @throws FluentException
     */
    final protected function string(string $name, int $len = 255, int $flag = self::STR_VARIABLE) : Column
    {
        // Check variability flag
        if(!in_array($flag, [self::STR_FIXED, self::STR_VARIABLE])) {
            // String size must be declared Fixed (char) or Variable (varchar)
            throw FluentException::badStringFlag();
        }

        // Create String column
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
     * @return Column
     */
    final protected function text(string $name) : Column
    {
        // Create Text column
        $this->columns[$name]   =   new Column;
        $this->columns[$name]->type =   "text";
        $this->columns[$name]->scalarType =   "string";
        $this->columns[$name]->attributes["length"] =   $len;

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
        // Create Enumeration column
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
        // Create double-precision floating-point numeric column
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
        // Create Decimal column
        $this->columns[$name]   =   new Column;
        $this->columns[$name]->type =   "decimal";
        $this->columns[$name]->scalarType =   "double";
        $this->columns[$name]->attributes["m"] =   $m;
        $this->columns[$name]->attributes["d"] =   $d;

        // Return Column object for further attribution
        return  $this->columns[$name];
    }

}