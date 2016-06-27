<?php

namespace Comely\IO\Database\Sample;

use Comely\IO\Database\Fluent;

/**
 * Sample Fluent Model
 * @package Comely\IO\Database\Sample
 */
class Model extends Fluent
{
    /**
     * This constant must be defined with name of table that this model belongs to.
     * Full (table) class name should be specified here. Alternatively, specifying table name (i.e. "table" or "sample")
     * will work too but this may cause conflicts when working with multiple databases
     */
    const SCHEMA_TABLE  =   "Comely\\IO\\Database\\Sample\\Table";

    /**
     * This could either be Array or a CSV string with names of columns that should not be filled automatically
     * @var string
     */
    protected $excludeKeys  =   "";
}

