<?php
declare(strict_types=1);

namespace Comely\IO\Database;

use Comely\IO\Database\Traits\DatabaseTrait;
use Comely\IO\Database\Traits\ErrorsTrait;
use Comely\IO\Database\Traits\LastQueryTrait;
use Comely\IO\Database\Traits\PaginationTrait;
use Comely\IO\Database\Traits\QueryBuilderTrait;

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

    protected $pdo;
    protected $config;
    protected $errors;
    protected $queryBuilder;

    public $lastQuery;

    use DatabaseTrait;
    use ErrorsTrait;
    use LastQueryTrait;
    use QueryBuilderTrait;
    use PaginationTrait;

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
        $this->queryBuilder =   new QueryBuilder();
        $this->errors   =   [];
        $this->config   =   new \stdClass;
        $this->config->persistent   =   true;
        $this->config->silentMode   =   false;
        $this->config->fetchCount   =   self::FETCH_COUNT_DEFAULT;

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
     * Prevent waking up from "serialized" state/sleep
     */
    private function __wakeUp()
    {
    }
}