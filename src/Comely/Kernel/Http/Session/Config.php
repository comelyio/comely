<?php
declare(strict_types=1);

namespace Comely\Kernel\Http\Session;

/**
 * Class Config
 * @package Comely\Kernel\Http\Session
 */
class Config
{
    public $dbTableName;
    public $gcProbability;

    /**
     * Config constructor.
     */
    public function __construct()
    {
        $this->dbTableName  =   "sessions"; // Name of table in Database
        $this->gcProbability    =   10; // Should be within 1 and 100
    }
}