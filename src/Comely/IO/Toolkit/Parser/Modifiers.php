<?php
declare(strict_types=1);

namespace Comely\IO\Toolkit\Parser;

/**
 * Class Modifiers
 * @package Comely\IO\Toolkit\Parser
 */
class Modifiers
{
    /**
     * @param $val
     * @return string
     */
    public function strtoupper($val) : string
    {
        return strtoupper(strval($val));
    }

    /**
     * @param $val
     * @return string
     */
    public function strtolower($val) : string
    {
        return strtolower(strval($val));
    }

    /**
     * @param $val
     * @return string
     */
    public function basename($val) : string
    {
        return basename(strval($val));
    }

    /**
     * @param $val
     * @return string
     */
    public function trim($val) : string
    {
        return trim(strval($val));
    }

    /**
     * @param $val
     * @return string
     */
    public function ucfirst($val) : string
    {
        return ucfirst(strval($val));
    }

    /**
     * @param $val
     * @return string
     */
    public function ucwords($val) : string
    {
        return ucwords(strval($val));
    }
}