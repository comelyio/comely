<?php
declare(strict_types=1);

namespace Comely\IO\Database\Traits;

/**
 * Class LastQueryTrait
 * @package Comely\IO\Database\Traits
 */
trait LastQueryTrait
{
    /**
     * Reset lastQuery information
     */
    public function resetLastQuery()
    {
        $this->lastQuery    =   (object) ["query" => "", "rows" => 0, "error" => null];
    }

    /**
     * Get number of rows affected/fetched
     *
     * @return int
     */
    public function rowCount() : int
    {
        if(property_exists($this, "lastQuery")  &&  is_object($this->lastQuery)) {
            if(property_exists($this->lastQuery, "rows")) {
                return (int) $this->lastQuery->rows;
            }
        }

        return 0;
    }
}