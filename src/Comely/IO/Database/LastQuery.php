<?php
declare(strict_types=1);

namespace Comely\IO\Database;

/**
 * Class LastQuery
 * @package Comely\IO\Database
 */
class LastQuery
{
    /** @var string|null */
    public $query;
    /** @var int */
    public $rows;
    /** @var string|null */
    public $error;

    /**
     * LastQuery constructor.
     */
    public function __construct()
    {
        $this->reset();
    }

    /**
     * Resets LastQuery object
     */
    public function reset()
    {
        $this->query    =   null;
        $this->rows =   0;
        $this->error    =   null;
    }
}