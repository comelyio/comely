<?php
declare(strict_types=1);

namespace Comely\IO\Database\Traits;

use Comely\IO\Database\DatabaseException;

/**
 * Class ErrorsTrait
 * @package Comely\IO\Database\Traits
 */
trait ErrorsTrait
{
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
}