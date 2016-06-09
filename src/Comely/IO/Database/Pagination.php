<?php
declare(strict_types=1);

namespace Comely\IO\Database;

/**
 * Class Pagination
 * @package Comely\IO\Database
 */
class Pagination implements \Countable
{
    public $totalRows;
    public $totalPages;
    public $start;
    public $limit;
    public $rows;
    public $pages;
    public $count;

    /**
     * Pagination constructor.
     * 
     * @param int $start
     * @param int $limit
     */
    public function __construct(int $start, int $limit)
    {
        $this->totalRows	=	0;
        $this->totalPages	=	0;
        $this->count	=	0;
        $this->start	=	$start;
        $this->limit	=	$limit;
        $this->rows	=	[];
        $this->pages	=	[];
    }

    /**
     * @return mixed
     */
    public function count()
    {
        return $this->count;
    }
}