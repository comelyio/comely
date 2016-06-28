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
     * While automatically mapping/populating this model by AbstractTable::findBy[col*] methods, all defined properties
     * will be publicly accessible regardless of visibility keyword (public|protected|private) used to define them.
     * Columns/keys that are not defined will be mapped as private, and can only be accessed via Fluent::getPrivate()
     * method.
     *
     * NOTE: Properties must be defined in camelCase, since Fluent automatically converts snake_case column names to
     * camelCase
     */
    public $var1, $var2, $var3;

    /**
     * This method is called automatically after a model is populated by AbstractTable::findBy[col*] methods.
     * Use this method to create formatted dates from time stamps or further modify mapped data and/or add or remove
     * values as necessary.
     */
    public function callBack()
    {
        // Your logic here
    }
}

