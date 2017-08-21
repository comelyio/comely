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

namespace Comely\IO\Database\Prototype;

use Comely\IO\Database\Exception\SchemaException;
use Comely\IO\Database\Schema\AbstractTable;

/**
 * Sample Table
 *
 * This sample table can be loaded by:
 * Schema::loadTable($db, "Comely\IO\Database\Sample\Table");
 * where $db is reference to Comely\IO\Database\Database instance
 *
 * @package Comely\IO\Database\Prototype
 */
class Table extends AbstractTable
{
    /**
     * Name of table to create in database
     * Provide a (snake_case) lowercase alphanumeric String
     * This must NOT be prefixed with database name, example "products" is valid but "db.products" is not
     *
     * Usually, this should be same as class name (but in lowercase)
     */
    const SCHEMA_TABLE  =   "table";
    
    /**
     * Establish a relationship with "Fluent" model by providing class name (with namespaces) or NULL
     * If NULL, then magic ::findBy[col*] methods will return array instead of Model
     *
     * NOTE: Empty string is not same as NULL, and will cause an error
     */
    const SCHEMA_MODEL  =   "Comely\\IO\\Database\\Sample\\Model";

    /**
     * This method is called automatically when a table is loaded.
     * @throws SchemaException
     */
    public function createTable()
    {
        // Define all columns here according to documentation
    }
}