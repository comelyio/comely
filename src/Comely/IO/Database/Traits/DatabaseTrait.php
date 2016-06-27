<?php
declare(strict_types=1);

namespace Comely\IO\Database\Traits;
use Comely\IO\Database\DatabaseException;

/**
 * Class DatabaseTrait
 * @package Comely\IO\Database\Traits
 */
trait DatabaseTrait
{
    /**
     * @param string $query
     * @param array $data
     * @param int $flag Database::QUERY_* flag
     * @throws DatabaseException
     */
    public function query(string $query, array $data, int $flag = 8)
    {
        return parent::pdoQuery(__METHOD__, $query, $data, $flag);
    }

    /**
     * Inserts a row
     *
     * @param array $row
     * @return int
     * @throws \Comely\IO\Database\DatabaseException
     */
    public function insert(array $row) : int
    {
        $keys   =   "";
        $values =   "";

        // Retrieve keys and values from passed row
        foreach($row as $key => $value) {
            if(is_int($key)) {
                // Error if an indexed key was found
                $this->error(__METHOD__, sprintf('Cannot accept an indexed array'));
                return 0;
            }

            // Append to keys and values strings
            $keys   .=  sprintf("`%s`, ", $key);
            $values .=  sprintf(":%s, ", $key);
        }

        // Compile INSERT query
        $query  =   sprintf(
            'INSERT INTO `%1$s` (%2$s) VALUES (%3$s)',
            $this->queryBuilder->tableName,
            substr($keys, 0, -2),
            substr($values, 0, -2)
        );

        // Execute INSERT query
        $insert =   $this->pdoQuery(__METHOD__, $query, $row, self::QUERY_EXEC);

        // Return last inserted row ID or 0
		return ($insert === true) ? (int) $this->lastInsertId() : 0;
    }

    /**
     * Update row(s)
     *
     * @param array $cols
     * @return int
     * @throws \Comely\IO\Database\DatabaseException
     */
    public function update(array $cols) : int
    {
        // Check if WHERE clause was set
        if($this->queryBuilder->whereClause === "1") {
            $this->error(__METHOD__, "UPDATE statement requires WHERE clause");
            return 0;
        }

        // SET clause for UPDATE statement
        $setClause  =   "";
        foreach($cols as $key => $value) {
            if(is_int($key)) {
                // Error if an indexed key was found
                $this->error(__METHOD__, "Cannot accept an indexed array");
                return 0;
            }

            $setClause  .=  sprintf("`%s`=:%s, ", $key, $key);
        }

        // Merge columns passed to this method with the ones passed to WHERE clause
        $queryData =   $cols;
        foreach((array) $this->queryBuilder->queryData as $key => $value) {
            // WHERE clause mustn't have numeric keys for Update method
            if(is_int($key)) {
                $this->error(__METHOD__, "WHERE clause requires named parameters");
                return 0;
            }

            // Keys from WHERE clause will be prefixed with "_"
            $queryData["_" . $key] =   $value;
        }

        // Compile UPDATE query
        $query  =   sprintf(
            'UPDATE `%1$s` SET %2$s WHERE %3$s',
            $this->queryBuilder->tableName,
            substr($setClause, 0, -2),
            str_replace(":", ":_", $this->queryBuilder->whereClause)
        );

        // Execute UPDATE query
        $update =   $this->pdoQuery(__METHOD__, $query, $queryData, self::QUERY_EXEC);

        // Return number of rows affected or 0
        return ($update	===	true) ? $this->lastQuery->rows : 0;
    }

    /**
     * Delete row(s)
     *
     * @return int
     * @throws \Comely\IO\Database\DatabaseException
     */
    public function delete() : int
    {
        // Check if WHERE clause was set
        if($this->queryBuilder->whereClause === "1") {
            $this->error(__METHOD__, "DELETE statement requires WHERE clause");
            return 0;
        }

        // Compile DELETE query
        $query  =   sprintf(
            'DELETE FROM `%1$s` WHERE %2$s',
            $this->queryBuilder->tableName,
            $this->queryBuilder->whereClause
        );

        // Execute DELETE query
        $delete =   $this->pdoQuery(__METHOD__, $query, $this->queryBuilder->queryData, self::QUERY_EXEC);

        // Return number of rows deleted or 0
        return ($delete === true) ? $this->lastQuery->rows : 0;
    }

    /**
     * Fetch rows from database
     *
     * @return array|bool
     */
    public function fetchAll()
    {
        // place a SELECT ... FOR UPDATE lock if lock() was explicitly called
        $lock	=	($this->queryBuilder->selectLock	===	true) ? " FOR UPDATE" : "";

        // Set LIMIT clause with start() or limit() methods were used
        $limit	=	"";
        if(is_int($this->queryBuilder->selectLimit)) {
            if(is_int($this->queryBuilder->selectStart)) {
                $limit	=	sprintf(" LIMIT %d,%d", $this->queryBuilder->selectStart, $this->queryBuilder->selectLimit);
            } else {
                $limit	=	sprintf(" LIMIT %d", $this->queryBuilder->selectLimit);
            }
        }

        // Compile SELECT query
        $query  =   sprintf(
            'SELECT %2$s FROM `%1$s` WHERE %3$s%4$s%5$s%6$s',
            $this->queryBuilder->tableName,
            $this->queryBuilder->selectColumns,
            $this->queryBuilder->whereClause,
            $this->queryBuilder->selectOrder,
            $limit,
            $lock
        );

        // Execute SELECT query
        $fetch  =   $this->pdoQuery(__METHOD__, $query, $this->queryBuilder->queryData, self::QUERY_FETCH);

        // Return fetched Array or Bool FALSE
        return is_array($fetch) ? $fetch : false;
    }

    /**
     * Fetch First
     *
     * This method calls fetchAll() method and returns first row
     *
     * @return array|bool
     */
    public function fetchFirst()
    {
        $fetch	=	$this->fetchAll();
        if(is_array($fetch)	&&	array_key_exists(0, $fetch)) {
            return $fetch[0];
        }

        return false;
    }

    /**
     * Fetch Last
     *
     * This method calls fetchAll() method and returns last row
     *
     * @return array|bool
     */
    public function fetchLast()
    {
        $fetch	=	$this->fetchAll();
        return (is_array($fetch)) ? end($fetch) : false;
    }
}