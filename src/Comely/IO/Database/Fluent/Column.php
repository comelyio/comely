<?php
declare(strict_types=1);

namespace Comely\IO\Database\Fluent;
use Comely\IO\Database\Exception\FluentException;

/**
 * Class Column
 * @package Comely\IO\Database\Fluent
 */
class Column
{
    public $type;
    public $scalarType;
    public $flag;
    public $default;
    public $attributes;

    /**
     * Column constructor.
     * @param bool $defaultAttributes
     */
    public function __construct($defaultAttributes = true)
    {
        $this->attributes  =   [];
        $this->default  =   null;

        // Default attributes
        if($defaultAttributes   === true) {
            $this->attributes["charset"]    =   "utf8";
            $this->attributes["collation"]  =   "utf8_general_ci";
        }
    }

    /**
     * Sets default value for column
     * 
     * Booleans are converted to 1 (true) or 0 (false)
     *
     * @param $value
     * @return Column
     * @throws FluentException
     */
    public function defaultValue($value) : self
    {
        // Check if given value is Boolean
        if(is_bool($value)) {
            // Convert Boolean to Integer
            $value  =   (int) $value;
        }

        // Cross check given value type with scalarType of column
        if(gettype($value)  !== $this->scalarType) {
            throw FluentException::badDefaultValue(gettype($value), $this->scalarType);
        }

        // Save default value
        $this->default  =   $value;
    }

    /**
     * Sets an attribute
     *
     * @param string $key
     * @param $value
     * @return Column
     */
    public function setAttr(string $key, $value) : self
    {
        $this->attributes[$key] =   $value;
        return $this;
    }

    /**
     * Sets "charset" attribute
     *
     * @param string $charSet
     * @return Column
     */
    public function charSet(string $charSet) : self
    {
        $this->attributes["charset"]    =   $charSet;
        return $this;
    }

    /**
     * Sets "collation" attribute
     *
     * @param string $collation
     * @return Column
     */
    public function collation(string $collation) : self
    {
        $this->attributes["collation"]  =   $collation;
        return $this;
    }

    /**
     * Sets "ZEROFILL" attribute
     *
     * @return Column
     */
    public function zeroFill() : self
    {
        $this->attributes["zerofill"]   =   1;
        return $this;
    }

    /**
     * Sets "signed" attribute to 1 for SIGNED values
     *
     * @return Column
     */
    public function signed() : self
    {
        $this->attributes["signed"] =   1;
        return $this;
    }

    /**
     * Sets "signed" attribute to 0 for SIGNED values
     *
     * @return Column
     */
    public function unSigned() : self
    {
        $this->attributes["signed"] =   0;
        return $this;
    }

    /**
     * Sets "primary" attribute for PRIMARY KEY
     *
     * If column type is integer, this will also set "ai" attribute (auto_increment)
     *
     * @return Column
     */
    public function primaryKey() : self
    {
        $this->attributes["primary"]    =   1;

        // Check if column type is INT
        if($this->type  === "int") {
            // Set "ai" attribute for auto_increment
            $this->attributes["ai"] =   1;
        }

        return $this;
    }

    /**
     * Sets "nullable" attribute
     *
     * Without "nullable" attribute, column will have "NOT NULL" in sql definition
     *
     * @return Column
     */
    public function nullable() : self
    {
        $this->attributes["nullable"]   =   1;
        return $this;
    }
}

