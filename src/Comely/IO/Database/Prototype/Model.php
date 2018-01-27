<?php
/**
 * This file is part of Comely IO package.
 * https://github.com/comelyio/comely
 *
 * Copyright (c) 2016-2018 Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/comelyio/comely/blob/master/LICENSE
 */

declare(strict_types=1);

namespace Comely\IO\Database\Prototype;

use Comely\IO\Database\Fluent;

/**
 * Class Model
 * @package Comely\IO\Database\Prototype
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
    public $var1;
    public $var2;
    public $var3;

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

