<?php
declare(strict_types=1);

namespace Comely\IO\Database;

/**
 * Class Database
 * @package Comely\IO\Database
 */
class Database extends AbstractPdo
{
    /** Use \PDOStatement::rowCount() for Fetch/select queries */
    const FETCH_COUNT_DEFAULT   =   1;
    /** Count resulting array using \count() for Fetch/select queries */
    const FETCH_COUNT_ARRAY =   2;
    /** Used with query() method to FETCH results from query */
    const QUERY_FETCH   =   4;
    /** User with query() method to EXEC query only, without fetching rows */
    const QUERY_EXEC    =   8;

    /** @var Config */
    protected $config;
    /** @var array */
    protected $errors;
    /** @var QueryBuilder */
    protected $queryBuilder;
    /** @var LastQuery */
    public $lastQuery;

    /**
     * Database constructor.
     *
     * Establish a new connection with a database using PDO
     *
     * @param string $driver
     * @param string $dbName
     * @param string $host
     * @param string|null $user
     * @param string|null $pass
     * @param bool $persistent
     * @throws DatabaseException
     */
    public function __construct(
        string $driver,
        string $dbName,
        string $host = "localhost",
        string $user = null,
        string $pass = null,
        bool $persistent = false
    ) {
        $this->config   =   new Config();
        $this->queryBuilder =   new QueryBuilder();
        $this->lastQuery    =   new LastQuery();
        $this->errors   =   [];

        // Check if PDO extension is loaded, we need PDO
        if(!extension_loaded("pdo"))  {
            throw DatabaseException::connectionError("PDO extension is not loaded with PHP");
        }

        // Check if driver is available
        if(!in_array($driver, \PDO::getAvailableDrivers())) {
            throw DatabaseException::connectionError(
                sprintf('Passed driver "%1$s" is not an available PDO driver', $driver)
            );
        }

        // Generate DSN
        if($driver  === "sqlite") {
            // SQLite dsn
            $dsn    =   "sqlite:" . $dbName;
        } else {
            // Split host argument in actual hostname and port
            $host   =   explode(":", $host);
            $port   =    $host[1] ?? null;
            $dbHost =   $host[0];
            $dbPort =   (isset($port)) ? sprintf(";port=%d", $port) : "";

            // Generate DSN
            $dsn    =   sprintf("%s:host=%s%s;dbname=%s;charset=utf8mb4", $driver, $dbHost, $dbPort, $dbName);
        }

        // PDO connection options
        $options    =   [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION];
        if($persistent  === true) {
            $options[\PDO::ATTR_PERSISTENT] =   true;
            $this->config->persistent   =   true;
        }

        // Connect to PDO via AbstractPdo adapter
        parent::__construct($dsn, $user, $pass, $options);
        $this->config->driver   =   $driver;
    }

    /**
     * Sets a flag
     *
     * @param int $flag
     * @return bool
     */
    public function setFlag(int $flag) : bool
    {
        // Fetch count flags
        if(in_array($flag, [self::FETCH_COUNT_ARRAY,self::FETCH_COUNT_DEFAULT], true)) {
            $this->config->fetchCount   =   $flag;
            return true;
        }

        // Nowhere to set passed flag
        return false;
    }

    /**
     * Get name of database driver
     *
     * @return string
     */
    public function driver() : string
    {
        return $this->config->driver;
    }

    /**
     * Is it a persistent connection?
     *
     * @return bool
     */
    public function isPersistent() : bool
    {
        return $this->config->persistent;
    }

    /**
     * @return string
     */
    public function __toString() : string
    {
        return __CLASS__;
    }

    /**
     * Prevent cloning
     */
    private function __clone()
    {
    }

    /**
     * Prevent sleeping
     */
    public function __sleep()
    {
    }

    /**
     * Error Handling
     */

    /**
     * Handle an error message
     *
     * @param string $method
     * @param string $error
     * @throws DatabaseException
     */
    protected function error(string $method, string $error)
    {
        $this->errors[] =   $error;
        if(property_exists($this, "lastQuery")  &&  is_object($this->lastQuery)) {
            if(property_exists($this->lastQuery, "error")) {
                $this->lastQuery->error =   $error;
            }
        }

        if(!$this->config->silentMode) {
            throw DatabaseException::queryError($method, $error);
        }
    }

    /**
     * Get all logged errors
     *
     * @return array
     */
    public function errors() : array
    {
        return $this->errors;
    }

    /**
     * Get the last logged error message
     *
     * @return string|false
     */
    public function lastError()
    {
        return end($this->errors);
    }

    /**
     * Enable silentMode
     */
    public function silentMode()
    {
        $this->config->silentMode   =   true;
    }

    /**
     * Disable silentMode
     */
    public function exceptionMode()
    {
        $this->config->silentMode   =   false;
    }

    /**
     * Database
     */

    /**
     * Execute a SQL query
     *
     * @param string $query
     * @param array $data
     * @param int $flag
     * @return bool|array
     * @throws DatabaseException
     */
    public function query(string $query, array $data, int $flag = 8)
    {
        return parent::pdoQuery(__METHOD__, $query, $data, $flag);
    }

    /**
     * Insert a row
     *
     * @param array $row
     * @return int
     * @throws DatabaseException
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
     * @throws DatabaseException
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
     * @throws DatabaseException
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
     * This method calls fetchAll() method and returns last row
     *
     * @return array|bool
     */
    public function fetchLast()
    {
        $fetch	=	$this->fetchAll();
        return (is_array($fetch)) ? end($fetch) : false;
    }

    /**
     * LastQuery
     */

    /**
     * Reset lastQuery information
     */
    public function resetLastQuery()
    {
        $this->lastQuery->reset();
    }

    /**
     * Get number of rows affected/fetched
     *
     * @return int
     */
    public function rowCount() : int
    {
        return $this->lastQuery->rows;
    }

    /**
     * Pagination
     */

    /**
     * @return Pagination
     */
    public function paginate() : Pagination
    {
        // Archive main query
        // Remove lock
        $this->queryBuilder->selectLock	=	false;
        $selectStart	=	(is_int($this->queryBuilder->selectStart)) ? $this->queryBuilder->selectStart : 0;
        $selectLimit	=	(is_int($this->queryBuilder->selectLimit)) ? $this->queryBuilder->selectLimit : 100;
        $selectTable	=	$this->queryBuilder->tableName;
        $selectColumns	=	$this->queryBuilder->selectColumns;
        $selectOrder	=	$this->queryBuilder->selectOrder;
        $whereClause	=	$this->queryBuilder->whereClause;
        $whereData	=	$this->queryBuilder->queryData;

        // Alter main query to find count(*)
        $this->queryBuilder->selectStart	=	null;
        $this->queryBuilder->selectLimit	=	null;
        $this->queryBuilder->selectColumns	=	"count(*)";

        // Get new instance of Comely\IO\Database\Pagination
        $pagination =   new Pagination($selectStart, $selectLimit);
        // Find total rows
        $totalRows	=	$this->fetchFirst();
        if(is_array($totalRows) &&  array_key_exists("count(*)", $totalRows)) {
            $totalRows	=	(int) $totalRows["count(*)"];
        }

        if($totalRows   >   0) {
            // Set number of rows and pages
            $pagination->totalRows	=	$totalRows;
            $pagination->totalPages	=	ceil($totalRows/$selectLimit);

            // Retrieve main query
            $this->queryBuilder->selectStart	=	$selectStart;
            $this->queryBuilder->selectLimit	=	$selectLimit;
            $this->queryBuilder->tableName	=	$selectTable;
            $this->queryBuilder->selectColumns	=	$selectColumns;
            $this->queryBuilder->selectOrder	=	$selectOrder;
            $this->queryBuilder->whereClause	=	$whereClause;
            $this->queryBuilder->queryData	=	$whereData;

            // Fetch rows in current page
            $rows	=	$this->fetchAll();
            if(is_array($rows)) {
                $pagination->rows	=	$rows;
                $pagination->count	=	count($rows);
            }

            // Build pages array
            for($i=0;$i<$pagination->totalPages;$i++) {
                $pagination->pages[]	=	["index" => $i+1, "start" => $i*$selectLimit];
            }
        }

        // Return Pagination instance
        return $pagination;
    }

    /**
     * QueryBuilder
     */

    /**
     * Sets name of DB table
     *
     * @param string $name
     * @return Database
     */
    public function table(string $name) : self
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
    public function where(string $clause, array $data) : self
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
    public function find(string $clause, array $data) : self
    {
        return $this->where($clause, $data);
    }

    /**
     * Columns to fetch from rows
     *
     * @param \string[] ...$cols
     * @return Database
     */
    public function select(string ...$cols) : self
    {
        $this->queryBuilder->selectColumns	=	implode(",", array_map(function($col) {
            return preg_match('/[\(|\)]/', $col) ? trim($col) : sprintf('`%1$s`', trim($col));
        }, $cols));

        return $this;
    }

    /**
     * Puts a SELECT ... FOR UPDATE lock
     *
     * @return Database
     */
    public function lock() : self
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
    public function orderAsc(string ...$cols) : self
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
    public function orderDesc(string ...$cols) : self
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
    public function start(int $start) : self
    {
        $this->queryBuilder->selectStart    =   $start;
        return $this;
    }

    /**
     * @param int $limit
     * @return Database
     */
    public function limit(int $limit) : self
    {
        $this->queryBuilder->selectLimit    =   $limit;
        return $this;
    }
}