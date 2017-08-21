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

namespace Comely\IO\Database;

/**
 * Class QueryBuilder
 * @package Comely\IO\Database
 */
class QueryBuilder
{
    /** @var string */
    public $tableName;
    /** @var string */
    public $whereClause;
    /** @var string */
    public $selectColumns;
    /** @var bool */
    public $selectLock;
    /** @var string */
    public $selectOrder;
    /** @var int|null */
    public $selectStart;
    /** @var int|null */
    public $selectLimit;
    /** @var array */
    public $queryData;

    /**
     * QueryBuilder constructor.
     */
    public function __construct()
    {
        $this->reset();
    }

    /**
     * Reset QueryBuilder
     */
    public function reset()
    {
        $this->tableName	=	"";
        $this->whereClause	=	"1";
        $this->selectColumns	=	"*";
        $this->selectLock	=	false;
        $this->selectOrder	=	"";
        $this->selectStart	=	null;
        $this->selectLimit	=	null;
        $this->queryData	=	[];
    }
}