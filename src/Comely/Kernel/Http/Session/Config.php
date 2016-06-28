<?php
declare(strict_types=1);

namespace Comely\Kernel\Http\Session;

/**
 * Class Config
 * @package Comely\Kernel\Http\Session
 */
class Config
{
    public $gcProbability;
    public $hashSalt;

    /**
     * Config constructor.
     */
    public function __construct()
    {
        $this->gcProbability    =   10; // Should be within 1 and 100
        $this->hashSalt =   "";
    }
}