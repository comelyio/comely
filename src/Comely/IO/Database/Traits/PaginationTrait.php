<?php
declare(strict_types=1);

namespace Comely\IO\Database\Traits;

use Comely\IO\Database\Pagination;

/**
 * Class PaginationTrait
 * @package Comely\IO\Database\Traits
 */
trait PaginationTrait
{
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
}