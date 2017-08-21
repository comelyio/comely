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
 * Class AbstractPdo
 * @package Comely\IO\Database
 */
abstract class AbstractPdo
{
    /** @var \PDO */
    protected $pdo;

    /**
     * AbstractPdo constructor.
     *
     * @param string $dsn
     * @param string|null $user
     * @param string|null $pass
     * @param array $options
     * @throws DatabaseException
     */
    public function __construct(string $dsn, string $user = null, string $pass = null, array $options)
    {
        try {
            $this->pdo  =   new \PDO($dsn, $user, $pass, $options);
        } catch(\PDOException $e) {
            throw DatabaseException::connectionError($e->getMessage());
        }
    }

    /**
     * Set a PDO attribute
     *
     * @param int $attr
     * @param $value
     * @return bool
     * @throws DatabaseException
     */
    public function pdoSetAttribute(int $attr, $value) : bool
    {
        try {
            return $this->pdo->setAttribute($attr, $value);
        } catch(\PDOException $e) {
            throw DatabaseException::pdoError($e->getMessage());
        }
    }

    /**
     * Get a PDO attribute
     *
     * @param int $attr
     * @return bool
     * @throws DatabaseException
     */
    public function pdoGetAttribute(int $attr)
    {
        try {
            return $this->pdo->getAttribute($attr);
        } catch(\PDOException $e) {
            throw DatabaseException::pdoError($e->getMessage());
        }
    }

    /**
     * Grab last inserted Id
     *
     * @param string|null $name
     * @return int
     * @throws DatabaseException
     */
    public function lastInsertId(string $name = null) : int
    {
        try {
            return (int) $this->pdo->lastInsertId($name);
        } catch(\PDOException $e) {
            throw DatabaseException::pdoError($e->getMessage());
        }
    }

    /**
     * Checks if inside a transaction
     *
     * @return bool
     * @throws DatabaseException
     */
    public function inTransaction() : bool
    {
        try {
            return $this->pdo->inTransaction();
        } catch(\PDOException $e) {
            throw DatabaseException::pdoError($e->getMessage());
        }
    }

    /**
     * Begins a transaction
     *
     * @throws DatabaseException
     */
    public function beginTransaction()
    {
        try {
            $began  =   $this->pdo->beginTransaction();
            if(!$began) {
                throw DatabaseException::pdoError("Failed to begin a transaction");
            }
        } catch(\PDOException $e) {
            throw DatabaseException::pdoError($e->getMessage());
        }
    }

    /**
     * Roll backs a transaction
     *
     * @throws DatabaseException
     */
    public function rollBack()
    {
        try {
            $cancel  =   $this->pdo->rollBack();
            if(!$cancel) {
                throw DatabaseException::pdoError("Failed to roll back transaction");
            }
        } catch(\PDOException $e) {
            throw DatabaseException::pdoError($e->getMessage());
        }
    }

    /**
     * Commits a transaction
     *
     * @throws DatabaseException
     */
    public function commit()
    {
        try {
            $commit  =   $this->pdo->commit();
            if(!$commit) {
                throw DatabaseException::pdoError("Failed to commit transaction");
            }
        } catch(\PDOException $e) {
            throw DatabaseException::pdoError($e->getMessage());
        }
    }

    /**
     * Finds explicit data type for the parameter using the PDO::PARAM_* constants
     *
     * @param $value
     * @return int
     */
    protected function bindValueType($value) : int
    {
        $valueType  =   gettype($value);
        switch($valueType) {
            case "boolean":
                return \PDO::PARAM_BOOL;
            case "integer":
                return \PDO::PARAM_INT;
            case "NULL":
                return \PDO::PARAM_NULL;
            default:
                return \PDO::PARAM_STR;
        }
    }

    /**
     * Prepare and execute a SQL query
     *
     * @param string $method
     * @param string $query
     * @param array $data
     * @param int $fetch Database::QUERY_* flag
     * @return bool
     * @throws DatabaseException
     */
    protected function pdoQuery(string $method, string $query, array $data, int $fetch = 8)
    {
        /** @var $this Database */
        // Reset QueryBuilder and lastQuery
        $this->resetLastQuery();
        $this->queryBuilder->reset();

        try {
            // Prepare a PDOStatement
            $stmnt  =   $this->pdo->prepare($query);
            $this->lastQuery->query =   $stmnt->queryString;

            // Bind params/values
            foreach($data as $key => $value) {
                // Indexed arrays get +1 to numeric keys
                if(is_int($key)) $key++;

                // Bind value
                $stmnt->bindValue($key, $value, $this->bindValueType($value));
            }

            // Execute PDOStatement
            $exec   =   $stmnt->execute();
            if($exec    === true    &&  $stmnt->errorCode() === "00000") {
                // Explicitly asked to fetch rows?
                if($fetch   === Database::QUERY_FETCH) {
                    $rows   =   $stmnt->fetchAll(\PDO::FETCH_ASSOC);
                    if(is_array($rows)) {
                        // Set rows param of lastQuery
                        if($this->config->fetchCount    === Database::FETCH_COUNT_ARRAY) {
                            // Count returned array
                            $this->lastQuery->rows  =   count($rows);
                        } else {
                            // Get rowCount from PDOStatement
                            $this->lastQuery->rows  =   (int) $stmnt->rowCount();
                        }

                        return $rows;
                    } else {
                        // PDOStatement::fetchAll() failed
                        return false;
                    }
                } else {
                    // Get rowCount from PDOStatement
                    $this->lastQuery->rows  =   (int) $stmnt->rowCount();

                    // Successfully executed statement
                    return true;
                }
            } else {
                // Query Failed
                $queryError =   $stmnt->errorInfo();
                $this->error($method, vsprintf('[%1$s-%2$s] %3$s', $queryError));
            }
        } catch(\PDOException $e) {
            $this->error($method, $e->getMessage());
        }

        // Silent mode should reach here on failure
        return false;
    }
}