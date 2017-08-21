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

namespace Comely\IO\Database\Schema\Table;

use Comely\IO\Database\Exception\SchemaException;

/**
 * Class Column
 * @package Comely\IO\Database\Schema\Table
 */
class Column
{
    /** @var null|string */
    public $type;
    /** @var null|string */
    public $scalarType;
    /** @var null|string|int */
    public $flag;
    /** @var null|string|int|double|float */
    public $default;
    /** @var array */
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
            $this->attributes["charset"]    =   "utf8mb4";
            $this->attributes["collation"]  =   "utf8mb4_unicode_ci";
        }
    }

    /**
     * @param string $name
     * @throws SchemaException
     */
    public static function checkName(string $name)
    {
        if(!preg_match("#^[a-z0-9_]+$#", $name)) {
            throw SchemaException::badColumnName($name);
        }
    }

    /**
     * Sets default value for column
     * 
     * Booleans are converted to 1 (true) or 0 (false)
     *
     * @param $value
     * @return Column
     * @throws SchemaException
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
            throw SchemaException::badDefaultValue(gettype($value), $this->scalarType);
        }

        // Save default value
        $this->default  =   $value;
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
     * Sets "ai" (auto-increment) attribute for column
     *
     * @return Column
     */
    public function autoIncrement() : self
    {
        $this->attributes["ai"] =   1;
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

    /**
     * Sets UNIQUE KEY attribute
     *
     * @return Column
     */
    public function unique() : self
    {
        $this->attributes["unique"]   =   1;
        return $this;
    }
}