<?php
declare(strict_types=1);

namespace Comely\IO\Database;

/**
 * Class Config
 * @package Comely\IO\Database
 */
class Config
{
    /** @var bool */
    public $persistent;
    /** @var bool */
    public $silentMode;
    /** @var int */
    public $fetchCount;
    /** @var string|null */
    public $driver;

    /**
     * Config constructor.
     */
    public function __construct()
    {
        $this->fetchCount   =   Database::FETCH_COUNT_DEFAULT;
        $this->persistent   =   true;
        $this->silentMode   =   false;
    }
}