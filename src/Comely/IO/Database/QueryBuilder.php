<?php
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