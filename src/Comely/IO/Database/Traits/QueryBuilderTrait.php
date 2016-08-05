<?php
declare(strict_types=1);

namespace Comely\IO\Database\Traits;

use Comely\IO\Database\Database;

/**
 * Class QueryBuilderTrait
 * @package Comely\IO\Database\Traits
 */
trait QueryBuilderTrait
{
    /**
     * Sets name of DB table
     *
     * @param string $name
     * @return Database
     */
    public function table(string $name) : Database
    {
        $this->queryBuilder->tableName  =   trim($name);
        return $this;
    }

    /**
     * Sets WHERE clause of SQL statement
     *
     * @param string $clause
     * @param array $data
     * @return Database
     */
    public function where(string $clause, array $data) : Database
    {
        $this->queryBuilder->whereClause    =   trim($clause);
        $this->queryBuilder->queryData    =   $data;
        return $this;
    }

    /**
     * Alias of where() method
     *
     * @param string $clause
     * @param array $data
     * @return Database
     */
    public function find(string $clause, array $data) : Database
    {
        return $this->where($clause, $data);
    }

    /**
     * Columns to fetch from rows
     *
     * @param \string[] ...$cols
     * @return Database
     */
    public function select(string ...$cols) : Database
    {
        $this->queryBuilder->selectColumns	=	implode(",", array_map(function($col) {
            return sprintf('`%1$s`', trim($col));
        }, $cols));

        return $this;
    }

    /**
     * Puts a SELECT ... FOR UPDATE lock
     *
     * @return Database
     */
    public function lock() : Database
    {
        $this->queryBuilder->selectLock =   true;
        return $this;
    }

    /**
     * Fetch in ascending order
     *
     * @param \string[] ...$cols
     * @return Database
     */
    public function orderAsc(string ...$cols) : Database
    {
        $cols   =   array_map(function($col) {
            return sprintf('`%1$s`', trim($col));
        }, $cols);

        $this->queryBuilder->selectOrder    =   sprintf(" ORDER BY %s ASC", trim(implode(",", $cols), ", "));
        return $this;
    }

    /**
     * Fetch in descending order
     *
     * @param \string[] ...$cols
     * @return Database
     */
    public function orderDesc(string ...$cols) : Database
    {
        $cols   =   array_map(function($col) {
            return sprintf('`%1$s`', trim($col));
        }, $cols);

        $this->queryBuilder->selectOrder    =   sprintf(" ORDER BY %s DESC", trim(implode(",", $cols), ", "));
        return $this;
    }

    /**
     * @param int $start
     * @return Database
     */
    public function start(int $start) : Database
    {
        $this->queryBuilder->selectStart    =   $start;
        return $this;
    }

    /**
     * @param int $limit
     * @return Database
     */
    public function limit(int $limit) : Database
    {
        $this->queryBuilder->selectLimit    =   $limit;
        return $this;
    }
}