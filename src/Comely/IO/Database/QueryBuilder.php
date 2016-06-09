<?php
declare(strict_types=1);

namespace Comely\IO\Database;

/**
 * Class QueryBuilder
 * @package Comely\IO\Database
 */
class QueryBuilder
{
    public $tableName;
    public $whereClause;
    public $selectColumns;
    public $selectLock;
    public $selectOrder;
    public $selectStart;
    public $selectLimit;
    public $queryData;

    /**
     * QueryBuilder constructor.
     */
    public function __construct()
    {
        // Bootstrap
        $this->resetQuery();
    }

    /**
     * Reset QueryBuilder
     */
    public function resetQuery()
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