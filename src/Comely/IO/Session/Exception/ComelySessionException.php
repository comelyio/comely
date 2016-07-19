<?php
declare(strict_types=1);

namespace Comely\IO\Session\Exception;

use Comely\IO\Session\SessionException;

/**
 * Class ComelySessionException
 * @package Comely\IO\Session\Exception
 */
class ComelySessionException extends SessionException
{
    protected static $componentId   =   "Comely\\IO\\Session\\ComelySession";

    /**
     * @param string $method
     * @param string $type
     * @return ComelySessionException
     */
    public static function badPropValue(string $method, string $type) : self
    {
        return new self($method, sprintf('Value type "%1$s" cannot be stored', $type), 1101);
    }
}