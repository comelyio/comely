<?php
declare(strict_types=1);

namespace Comely\Kernel\Http\Session;

use Comely\Kernel\Exception\SessionException;
use Comely\Kernel\Http\Session;

/**
 * Class ConfigTrait
 * @package Comely\Kernel\Http\Session
 */
trait ConfigTrait
{
    /**
     * Sets name of database table if storage engine is Comely\IO\Database\Database
     *
     * @param string $table
     * @return Session
     */
    public function setDbTableName(string $table) : Session
    {
        $this->config->dbTableName  =   $table;
        return $this;
    }

    /**
     * Sets probability of garbage collection
     *
     * Garbage collection method is called from the same registered shutdown (register_shutdown_function()) method
     * that saves session data back into storage
     *
     * @param int $prob
     * @return Session
     * @throws SessionException
     */
    public function setGcProbability(int $prob) : Session
    {
        if(!\Comely::intRange($prob, 1, 100)) {
            throw SessionException::configError("gcProbability", "Probability integer must be between 1 and 100");
        }

        $this->config->gcProbability    =   $prob;
        return $this;
    }
}